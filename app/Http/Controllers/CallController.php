<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Call;
use App\Models\CallParticipant;
use App\Events\CallInvited;
use App\Events\CallAccepted;
use App\Events\CallEnded;
class CallController extends Controller
{
    /**
     * Tạo cuộc gọi 1–1 và mời callee.
     * POST /calls/invite { user_id: int }
     * -> { id: int }
     */
    public function invite(Request $r)
    {
        $data = $r->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $caller = $r->user();
        $calleeId = (int) $data['user_id'];

        $call = null;

        DB::transaction(function () use (&$call, $caller, $calleeId) {
            $call = Call::create([
                'creator_id' => $caller->id,
                'type'       => 'direct',
                'status'     => 'ringing',
                'started_at' => now(),
            ]);

            CallParticipant::create([
                'call_id'   => $call->id,
                'user_id'   => $caller->id,
                'role'      => 'caller',
                'state'     => 'joined',
                'joined_at' => now(),
            ]);

            CallParticipant::create([
                'call_id' => $call->id,
                'user_id' => $calleeId,
                'role'    => 'callee',
                'state'   => 'ringing',
            ]);
        });

        $callerAvatar = $caller->avatar
            ? asset("images/client/avatar/{$caller->avatar}")
            : null;

        broadcast(new CallInvited($call->id, $calleeId, [
            'id' => $caller->id,
            'name' => $caller->name,
            'avatar' => $callerAvatar,
        ]));

        return response()->json(['id' => $call->id], 201);
    }

    /**
     * Callee chấp nhận cuộc gọi.
     * POST /calls/accept { id: int }
     */
    public function accept(Request $r)
    {
        $data = $r->validate([
            'id' => 'required|exists:calls,id',
        ]);

        $user = $r->user();
        $call = Call::findOrFail($data['id']);

        DB::transaction(function () use ($call, $user) {
            $p = CallParticipant::where('call_id', $call->id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            if ($p->state !== 'joined') {
                $p->update(['state' => 'joined', 'joined_at' => now()]);
            }

            $joinedCount = CallParticipant::where('call_id', $call->id)
                ->where('state', 'joined')->count();

            if ($joinedCount >= 2 && $call->status !== 'active') {
                $call->update(['status' => 'active']);
            }
        });

        broadcast(new CallAccepted($call->id, $user->id));

        return response()->noContent();
    }

    /**
     * Callee t���� ch����i cu��?c g��?i.
     * POST /calls/reject { id: int }
     */
    public function reject(Request $r)
    {
        $data = $r->validate([
            'id' => 'required|exists:calls,id',
        ]);

        $user = $r->user();
        $call = Call::findOrFail($data['id']);

        DB::transaction(function () use ($call, $user) {
            CallParticipant::where('call_id', $call->id)
                ->where('user_id', $user->id)
                ->update(['state' => 'declined', 'left_at' => now()]);

            if ($call->status !== 'ended') {
                $call->update(['status' => 'missed', 'ended_at' => now()]);
            }
        });

        broadcast(new CallEnded($call->id, $user->id));

        return response()->noContent();
    }

    /**
     * Kết thúc cuộc gọi.
     * POST /calls/end { id: int }
     */
    public function end(Request $r)
    {
        $data = $r->validate([
            'id' => 'required|exists:calls,id',
        ]);

        $user = $r->user();
        $call = Call::findOrFail($data['id']);

        DB::transaction(function () use ($call, $user) {
            CallParticipant::where('call_id', $call->id)
                ->where('user_id', $user->id)
                ->update(['state' => 'left', 'left_at' => now()]);

            if ($call->status !== 'ended') {
                $call->update(['status' => 'ended', 'ended_at' => now()]);
            }
        });

        broadcast(new CallEnded($call->id, $user->id));

        return response()->noContent();
    }

    /**
     * Trang hiển thị phòng gọi (nếu cần).
     * GET /calls/{id}
     */
    public function show($id)
    {
        $call = Call::findOrFail($id);
        $user = auth()->user();
        
        abort_unless(
            $call->participants()->where('user_id', $user->id)->exists(),
            403
        );

        // Nếu request là AJAX/API, trả về JSON
        if (request()->wantsJson() || request()->expectsJson()) {
            $participants = $call->participants()->with('user:id,name,avatar')->get();
            
            return response()->json([
                'call' => [
                    'id'         => $call->id,
                    'creator_id' => $call->creator_id,
                    'type'       => $call->type,
                    'status'     => $call->status,
                    'participants' => $participants->map(function ($p) {
                        return [
                            'user_id' => $p->user_id,
                            'role' => $p->role,
                            'state' => $p->state,
                        ];
                    }),
                ],
            ]);
        }

        // Nếu không, trả về Inertia view
        return inertia('CallRoom', [
            'call' => [
                'id'         => $call->id,
                'creator_id' => $call->creator_id,
                'type'       => $call->type,
                'status'     => $call->status,
            ],
            'me' => [
                'id'   => $user->id,
                'name' => $user->name ?? '',
            ],
        ]);
    }
}
