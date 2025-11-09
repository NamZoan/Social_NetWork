<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\SignalingMessage;

class SignalController extends Controller
{
    public function offer(Request $r)
    {
        $payload = $this->validatePayload($r);
        broadcast(new SignalingMessage($payload['id'], $payload['payload']));
        return response()->noContent();
    }

    public function answer(Request $r)
    {
        $payload = $this->validatePayload($r);
        broadcast(new SignalingMessage($payload['id'], $payload['payload']));
        return response()->noContent();
    }

    public function ice(Request $r)
    {
        $payload = $this->validatePayload($r);
        broadcast(new SignalingMessage($payload['id'], $payload['payload']));
        return response()->noContent();
    }

    private function validatePayload(Request $r): array
    {
        return $r->validate([
            'id' => 'required|exists:calls,id',
            'payload' => 'required|array',
        ]);
    }
}
