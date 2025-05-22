<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Events\SendMessage;
use App\Models\Notification;
use Carbon\Carbon;
use App\Repositories\ConversationRepositoryInterface;

class MessageController extends Controller
{
    protected $conversationRepo;

    public function __construct(ConversationRepositoryInterface $conversationRepo)
    {
        $this->conversationRepo = $conversationRepo;
    }

    public function index()
    {
        $user = Auth::user();
        $conversations = $this->conversationRepo->getUserConversations($user->id);
        return Inertia::render('Messages/Messages', [
            'conversations' => $conversations,
            'user' => $user
        ]);
    }

    public function getFriends()
    {
        $user = Auth::user();
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

            $conversation = $this->conversationRepo->createConversation([
                'conversation_type' => 'group',
                'name' => $request->name,
                'creator_id' => $user->id
            ]);

            $memberData = [];
            $memberData[$user->id] = [
                'role' => 'admin',
                'joined_at' => now()
            ];
            foreach ($members as $memberId) {
                if ($memberId != $user->id) {
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
        $conversation = $this->conversationRepo->getConversationById($conversationId);

        if (!$conversation || !$this->conversationRepo->isMember($conversationId, $user->id)) {
            abort(403, 'Unauthorized');
        }

        $messages = $this->conversationRepo->getMessages($conversationId);

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $sender = Auth::user();
        try {
            if ($request->has('conversation_id')) {
                $request->validate([
                    'conversation_id' => 'required|exists:conversations,id',
                    'content' => 'required|string',
                    'message_type' => 'required|string'
                ]);

                $conversationId = $request->conversation_id;
                if (!$this->conversationRepo->isMember($conversationId, $sender->id)) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }

                $message = $this->conversationRepo->addMessage($conversationId, [
                    'sender_id' => $sender->id,
                    'content' => $request->content,
                    'message_type' => $request->message_type ?? "text",
                    'sent_at' => now(),
                ]);
                $message->load('sender');

                $conversation = $this->conversationRepo->getConversationById($conversationId);
                $receivers = $conversation->members()->where('users.id', '!=', $sender->id)->get();

                // Tạo thông báo cho người nhận đầu tiên
                $notification = Notification::create([
                    'user_id' => $receivers->first()->id,
                    'type' => 'message',
                    'reference_id' => $message->id,
                    'reference_type' => 'message',
                    'sender_id' => $sender->id,
                    'message' => $request->content,
                    'created_at' => Carbon::now(),
                    'is_read' => false,
                    'action_url' => '/messages/' . $conversationId,
                    'data' => json_encode([
                        'message' =>$request->content,
                        'action_url' => '/messages/' . $conversationId
                    ])
                ]);

                // Broadcast sự kiện với thông báo
                broadcast(new SendMessage($message, $sender, $notification, $conversation))->toOthers();

                // Tạo thông báo cho các người nhận còn lại
                foreach ($receivers->skip(1) as $receiver) {
                    Notification::create([
                        'user_id' => $receiver->id,
                        'type' => 'message',
                        'reference_id' => $message->id,
                        'reference_type' => 'message',
                        'sender_id' => $sender->id,
                        'message' => $request->content,
                        'created_at' => Carbon::now(),
                        'is_read' => false,
                        'action_url' => '/messages/' . $conversationId,
                        'data' => json_encode([
                            'message' => $request->content,
                            'action_url' => '/messages/' . $conversationId
                        ])
                    ]);
                }

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
            $messageType = $request->input('message_type') ?? "text";

            // Tìm hoặc tạo cuộc trò chuyện cá nhân
            $conversation = $this->conversationRepo->findOrCreateIndividualConversation($sender->id, $recipientId);

            $message = $this->conversationRepo->addMessage($conversation->id, [
                'sender_id' => $sender->id,
                'content' => $content,
                'message_type' => $messageType,
                'sent_at' => now(),
            ]);
            $message->load('sender');

            // Tạo thông báo cho người nhận
            $notification = Notification::create([
                'user_id' => $recipientId,
                'type' => 'message',
                'reference_id' => $message->id,
                'reference_type' => 'message',
                'sender_id' => $sender->id,
                'message' => 'Bạn có tin nhắn mới từ ' . $request->content,
                'created_at' => Carbon::now(),
                'is_read' => false,
                'action_url' => '/messages/' . $conversation->id,
                'data' => json_encode([
                    'message' => 'Bạn có tin nhắn mới từ ' . $sender->name,
                    'action_url' => '/messages/' . $conversation->id
                ])
            ]);

            broadcast(new SendMessage($message, $sender, $notification, $conversation))->toOthers();

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

    public function getConversationMembers($conversationId)
    {
        $conversation = Conversation::with('members')->findOrFail($conversationId);
        // Trả về danh sách thành viên (id, name, avatar)
        $members = $conversation->members->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'avatar' => $member->avatar,
            ];
        });
        return response()->json($members);
    }
    public function addMembersToConversation(Request $request, $conversationId)
    {
        $request->validate([
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:users,id'
        ]);

        $conversation = Conversation::findOrFail($conversationId);

        // Chỉ cho phép thêm vào nhóm
        if ($conversation->conversation_type !== 'group') {
            return response()->json(['success' => false, 'message' => 'Chỉ có thể thêm thành viên vào nhóm!'], 400);
        }

        $now = now();
        $attachData = [];
        foreach ($request->member_ids as $memberId) {
            $attachData[$memberId] = [
                'role' => 'member',
                'joined_at' => $now
            ];
        }

        // Thêm các thành viên mới, không ảnh hưởng thành viên cũ
        $conversation->members()->syncWithoutDetaching($attachData);

        return response()->json(['success' => true, 'message' => 'Đã thêm thành viên vào nhóm!']);
    }
    public function leaveConversation($conversationId)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($conversationId);

        // Chỉ cho phép rời nhóm, không phải chat cá nhân
        if ($conversation->conversation_type !== 'group') {
            return response()->json(['success' => false, 'message' => 'Chỉ có thể rời nhóm!'], 400);
        }

        // Không cho phép creator rời nhóm (nếu muốn)
        if ($conversation->creator_id == $user->id) {
            return response()->json(['success' => false, 'message' => 'Người tạo nhóm không thể rời nhóm!'], 400);
        }

        // Xóa user khỏi nhóm
        $conversation->members()->detach($user->id);

        return back()->withInput();
    }
    public function removeMemberFromConversation($conversationId, $userId)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($conversationId);

        // Chỉ creator mới được xóa thành viên
        if ($conversation->creator_id != $user->id) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa thành viên!'], 403);
        }

        // Không cho phép xóa chính mình (creator)
        if ($user->id == $userId) {
            return response()->json(['success' => false, 'message' => 'Không thể tự xóa mình!'], 400);
        }

        $conversation->members()->detach($userId);

        return response()->json(['success' => true, 'message' => 'Đã xóa thành viên khỏi nhóm!']);
    }
    public function deleteConversation($conversationId)
    {
        $user = Auth::user();
        $this->conversationRepo->deleteConversation($conversationId, $user->id);

        return response()->json(['success' => true, 'message' => 'Đã xóa hội thoại khỏi danh sách của bạn!']);
    }
    public function updateConversation(Request $request, $conversationId)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($conversationId);

        if ($conversation->creator_id != $user->id) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền cập nhật nhóm này!'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        $conversation->name = $request->name;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/client/group/conversation'), $filename);
            $conversation->image = $filename;
        }

        $conversation->save();

        return response()->json(['success' => true, 'message' => 'Cập nhật nhóm thành công!', 'image' => $conversation->image]);
    }
    public function deleteMessage($messageId)
    {
        $user = Auth::user();
        $message = Message::findOrFail($messageId);

        // Chỉ cho phép xóa tin nhắn của chính mình
        if ($message->sender_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Bạn chỉ có thể xóa tin nhắn của mình!'], 403);
        }

        $message->delete();

        return response()->json(['success' => true]);
    }
}
