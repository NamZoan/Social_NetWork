<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Events\SendMessage;
class MessageController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $conversations = Conversation::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with([
                'members' => function ($query) use ($user) {
                    $query->where('user_id', '!=', $user->id);
                }
            ])
            ->with([
                'messages' => function ($query) {
                    $query->latest('sent_at')->take(20);
                }
            ])
            ->get();

        return Inertia::render('Messages/Messages', [
            'conversations' => $conversations,
            'user' => $user
        ]);
    }

    public function getFriends()
    {
        $user = Auth::user();

        // Lấy danh sách bạn bè của user
        $friends = User::whereHas('friends', function ($query) use ($user) {
            $query->where('friend_id', $user->id)
                ->where('status', 'accepted');
        })
            ->orWhereHas('friendOf', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'accepted');
            })
            ->select('id', 'name', 'avatar')
            ->get();

        return response()->json($friends);
    }

    public function createGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'members' => 'required|array|min:2',
            'members.*' => 'exists:users,id'
        ]);

        $user = Auth::user();
        $members = $request->input('members');

        try {
            DB::beginTransaction();

            // Tạo cuộc trò chuyện nhóm mới
            $conversation = Conversation::create([
                'conversation_type' => 'group',
                'name' => $request->name,
                'creator_id' => $user->id
            ]);

            // Thêm tất cả thành viên vào cuộc trò chuyện
            $memberData = [];

            // Thêm người tạo nhóm làm admin
            $memberData[$user->id] = [
                'role' => 'admin',
                'joined_at' => now()
            ];

            // Thêm các thành viên khác
            foreach ($members as $memberId) {
                if ($memberId != $user->id) { // Không thêm lại người tạo nhóm
                    $memberData[$memberId] = [
                        'role' => 'member',
                        'joined_at' => now()
                    ];
                }
            }

            $conversation->members()->attach($memberData);

            DB::commit();

            return response()->json([
                'success' => true,
                'conversation' => $conversation->load([
                    'members' => function ($query) use ($user) {
                        $query->where('user_id', '!=', $user->id);
                    }
                ])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo nhóm: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getMessages($conversationId)
    {
        $user = Auth::user();

        // Kiểm tra user có trong cuộc trò chuyện không
        $conversation = Conversation::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($conversationId);

        $messages = Message::where('conversation_id', $conversationId)
            ->with('sender')
            ->orderBy('sent_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $sender = Auth::user();
        try {
            // Nếu có conversation_id, đây là tin nhắn trong cuộc trò chuyện hiện có
            if ($request->has('conversation_id')) {
                $request->validate([
                    'conversation_id' => 'required|exists:conversations,id',
                    'content' => 'required|string',
                    'message_type' => 'required|string'
                ]);

                $conversation = Conversation::findOrFail($request->conversation_id);

                // Kiểm tra người dùng có trong cuộc trò chuyện không
                if (!$conversation->members()->where('user_id', $sender->id)->exists()) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }

                $message = $conversation->messages()->create([
                    'sender_id' => $sender->id,
                    'content' => $request->content,
                    'message_type' => "text",
                    'sent_at' => now(),
                ]);

                $message->load('sender');

                // Broadcast tin nhắn mới cho tất cả mọi người trong conversation
                broadcast(new \App\Events\SendMessage($message, $sender));

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'sender' => $sender
                ]);
            }

            // Nếu không có conversation_id, đây là tin nhắn mới
            $request->validate([
                'recipient_id' => 'required|exists:users,id',
                'content' => 'required|string',
            ]);

            $recipientId = $request->input('recipient_id');
            $content = $request->input('content');
            $messageType = $request->input('message_type');

            // 1. Tìm cuộc trò chuyện giữa 2 người
            $conversation = Conversation::where('conversation_type', 'individual')
                ->whereHas('members', fn($q) => $q->where('user_id', $sender->id))
                ->whereHas('members', fn($q) => $q->where('user_id', $recipientId))
                ->first();

            // 2. Nếu chưa có, tạo mới
            if (!$conversation) {
                $conversation = Conversation::create([
                    'conversation_type' => 'individual',
                    'creator_id' => $sender->id,
                ]);

                // Thêm thành viên vào cuộc trò chuyện
                $conversation->members()->attach([
                    $sender->id => ['role' => 'member', 'joined_at' => now()],
                    $recipientId => ['role' => 'member', 'joined_at' => now()]
                ]);
            }

            // 3. Tạo tin nhắn
            $message = $conversation->messages()->create([
                'sender_id' => $sender->id,
                'content' => $content,
                'message_type' => "text",
                'sent_at' => now(),
            ]);


            broadcast(new \App\Events\SendMessage($message, $sender))->toOthers();
            $message->load('sender');

            return response()->json([
                'success' => true,
                'message' => $message,
                'conversation' => $conversation->load([
                    'members' => function ($query) use ($sender) {
                        $query->where('user_id', '!=', $sender->id);
                    }
                ])
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi gửi tin nhắn: ' . $th->getMessage()
            ], 500);
        }

    }
}
