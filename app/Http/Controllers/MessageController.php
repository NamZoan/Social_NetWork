<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;

use Illuminate\Support\Facades\Auth;
use App\Events\SendMessage;
use App\Models\Notification;
use Carbon\Carbon;
use App\Repositories\ConversationRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    protected $conversationRepo;

    public function __construct(ConversationRepositoryInterface $conversationRepo)
    {
        $this->conversationRepo = $conversationRepo;
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->whereHas('members', fn($q) => $q->where('users.id', $user->id))
            ->with([
                'members:id,name,avatar',
                'lastMessage',
                'lastMessage.sender:id,name,avatar',
            ])
            ->withMax('messages as latest_sent_at', 'sent_at')
            ->orderByDesc('latest_sent_at')
            ->get(['id','name','image','conversation_type','creator_id','is_active']);

        return Inertia::render('Messages/Messages', [
            'user' => $user->only(['id','name']),
            'conversations' => $conversations,
            'selectedConversationId' => null,
            'messages' => [],
            'pagination' => [
                'has_more' => false,
                'total' => 0
            ],
        ]);
    }

    public function show(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        abort_unless($conversation->members()->where('users.id', $user->id)->exists(), 403);

        // Load initial messages (latest 30)
        $perPage = 10;
        $messagesQuery = $conversation->messages()
            ->with('sender:id,name,avatar')
            ->select(['id','sender_id','conversation_id','content','sent_at','message_type','attachment_url'])
            ->orderBy('sent_at', 'desc');

        $totalMessages = $messagesQuery->count();
        $messages = $messagesQuery->limit($perPage)->get();

        // Reverse order để hiển thị từ cũ đến mới
        $messages = $messages->reverse()->values();

        $conversations = Conversation::query()
            ->whereHas('members', fn($q) => $q->where('users.id', $user->id))
            ->with([
                'members:id,name,avatar',
                'lastMessage',
                'lastMessage.sender:id,name,avatar',
            ])
            ->withMax('messages as latest_sent_at', 'sent_at')
            ->orderByDesc('latest_sent_at')
            ->get(['id','name','image','conversation_type','creator_id','is_active']);

        return Inertia::render('Messages/Messages', [
            'user' => $user->only(['id','name']),
            'conversations' => $conversations,
            'selectedConversationId' => (int) $conversation->id,
            'messages' => $messages,
            'pagination' => [
                'has_more' => $totalMessages > $perPage,
                'total' => $totalMessages,
                'per_page' => $perPage
            ],
        ]);
    }

    public function getMessages(Request $request, Conversation $conversation)
    {
        // Bảo vệ: user phải là member
        abort_unless($conversation->members()->where('users.id', $request->user()->id)->exists(), 403);
        $limit = min(10, max(30, (int)$request->query('limit', 10)));
        $beforeId = $request->query('before_id'); // ID của tin nhắn cũ nhất hiện có

        $query = $conversation->messages()
            ->with('sender:id,name,avatar')
            ->select(['id','sender_id','conversation_id','content','sent_at','message_type','attachment_url'])
            ->orderBy('sent_at', 'desc')
            ->orderBy('id', 'desc');

        // Nếu có beforeId, lấy những tin nhắn cũ hơn
        if ($beforeId) {
            $beforeMessage = Message::find($beforeId);
            if ($beforeMessage) {
                $query->where(function($q) use ($beforeMessage) {
                    $q->where('sent_at', '<', $beforeMessage->sent_at)
                      ->orWhere(function($subQ) use ($beforeMessage) {
                          $subQ->where('sent_at', '=', $beforeMessage->sent_at)
                               ->where('id', '<', $beforeMessage->id);
                      });
                });
            }
        }

        $messages = $query->limit($limit + 1)->get();
        $hasMore = $messages->count() > $limit;

        if ($hasMore) {
            $messages->pop(); // Remove extra message
        }

        // Sort lại tăng dần theo thời gian để render
        $messages = $messages->sortBy(['sent_at', 'id'])->values();

        return response()->json([
            'messages' => $messages,
            'has_more' => $hasMore,
        ]);
    }

    
public function sendMessage(Request $request)
{
    $sender = Auth::user();

    try {
        if ($request->has('conversation_id')) {
            // ... (Phần validate và kiểm tra conversation giữ nguyên) ...
            $request->validate([
                'conversation_id' => 'required|exists:conversations,id',
                'content' => 'required_without:images|string|nullable',
                'images' => 'nullable|array|max:10', // Tối đa 10 ảnh
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max per image
                'captions' => 'nullable|array',
                'captions.*' => 'string|nullable|max:500',
            ]);

            $conversationId = $request->conversation_id;
            if (!$this->conversationRepo->isMember($conversationId, $sender->id)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $conversation = $this->conversationRepo->getConversationById($conversationId);
            $receivers = $conversation->members()->where('users.id', '!=', $sender->id)->get();

            $lastCreatedMessage = null;

            // ... (Phần gửi tin nhắn VĂN BẢN giữ nguyên) ...
            if ($request->filled('content')) {
                $textData = [
                    'sender_id' => $sender->id,
                    'content' => $request->input('content'),
                    'message_type' => 'text',
                    'sent_at' => now(),
                ];

                $lastCreatedMessage = $this->conversationRepo->addMessage($conversationId, $textData);
                $lastCreatedMessage->load(['sender:id,name,avatar']);

                // ... (Phần tạo notification và broadcast cho text giữ nguyên) ...
                foreach ($receivers as $receiver) {
                    $notification = Notification::create([
                        'user_id' => $receiver->id,
                        'type' => 'message',
                        'reference_id' => $lastCreatedMessage->id,
                        'reference_type' => 'message',
                        'sender_id' => $sender->id,
                        'message' => $textData['content'],
                        'created_at' => Carbon::now(),
                        'is_read' => false,
                        'action_url' => '/messages/' . $conversationId,
                        'data' => json_encode([
                            'message' => $textData['content'],
                            'action_url' => '/messages/' . $conversationId
                        ])
                    ]);
                    broadcast(new SendMessage($lastCreatedMessage, $sender, $notification, $conversation))->toOthers();
                }
            }


            // === BẮT ĐẦU THAY ĐỔI 1 ===
            // Xử lý upload ảnh (cho cuộc trò chuyện CÓ SẴN)
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $captions = $request->input('captions', []);

                // Đây là đường dẫn tương đối mà chúng ta muốn
                $relativeSavePath = 'images/client/message'; 
                // Đây là đường dẫn vật lý (tuyệt đối) để 'move' tệp
                $destinationPath = public_path($relativeSavePath);

                // Đảm bảo thư mục tồn tại
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                foreach ($images as $index => $image) {
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($image->getMimeType(), $allowedMimes)) {
                        continue; // Bỏ qua tệp không hợp lệ
                    }

                    $filename = time() . '_' . Str::random(10) . '_' . $index . '.' . $image->getClientOriginalExtension();
                    
                    // Di chuyển tệp vào thư mục public
                    $image->move($destinationPath, $filename);

                    // Tạo đường dẫn tương đối để lưu vào CSDL
                    $path = $relativeSavePath . '/' . $filename;

                    $imageData = [
                        'sender_id' => $sender->id,
                        'content' => $captions[$index] ?? '',
                        'message_type' => 'image',
                        'attachment_url' => $path, // Lưu đường dẫn tương đối
                        'sent_at' => now(),
                    ];

                    $createdImageMessage = $this->conversationRepo->addMessage($conversationId, $imageData);
                    $createdImageMessage->load(['sender:id,name,avatar']);
                    $lastCreatedMessage = $createdImageMessage;

                    // ... (Phần tạo notification và broadcast cho ảnh giữ nguyên) ...
                    foreach ($receivers as $receiver) {
                        $notificationMessage = $imageData['content'] ? $imageData['content'] : ($sender->name . ' đã gửi một hình ảnh');
                        $notification = Notification::create([
                            'user_id' => $receiver->id,
                            'type' => 'message',
                            'reference_id' => $createdImageMessage->id,
                            'reference_type' => 'message',
                            'sender_id' => $sender->id,
                            'message' => $notificationMessage,
                            'created_at' => Carbon::now(),
                            'is_read' => false,
                            'action_url' => '/messages/' . $conversationId,
                            'data' => json_encode([
                                'message' => $notificationMessage,
                                'action_url' => '/messages/' . $conversationId
                            ])
                        ]);
                        broadcast(new SendMessage($createdImageMessage, $sender, $notification, $conversation))->toOthers();
                    }
                }
            }
            // === KẾT THÚC THAY ĐỔI 1 ===

            return response()->json([
                'success' => true,
                'message' => $lastCreatedMessage,
                'sender' => $sender
            ]);
        }

        // --- Tin nhắn mới (conversation chưa tồn tại) ---
        
        // ... (Phần validate cho tin nhắn mới giữ nguyên) ...
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'content' => 'required_without:images|string|nullable',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'captions' => 'nullable|array',
            'captions.*' => 'string|nullable|max:500',
        ]);

        $recipientId = $request->input('recipient_id');
        $content = $request->input('content') ?? '';

        $conversation = $this->conversationRepo->findOrCreateIndividualConversation($sender->id, $recipientId);
        $lastCreatedMessage = null;

        // ... (Phần gửi tin nhắn VĂN BẢN cho tin mới giữ nguyên) ...
        if (!empty($content)) {
            $messageData = [
                'sender_id' => $sender->id,
                'content' => $content,
                'message_type' => 'text',
                'sent_at' => now(),
            ];

            $lastCreatedMessage = $this->conversationRepo->addMessage($conversation->id, $messageData);
            $lastCreatedMessage->load('sender');

            // ... (Phần notification, broadcast cho text giữ nguyên) ...
            $notificationMessage = $content;
            $notification = Notification::create([
                'user_id' => $recipientId,
                'type' => 'message',
                'reference_id' => $lastCreatedMessage->id,
                'reference_type' => 'message',
                'sender_id' => $sender->id,
                'message' => $notificationMessage,
                'created_at' => Carbon::now(),
                'is_read' => false,
                'action_url' => '/messages/' . $conversation->id,
                'data' => json_encode([
                    'message' => $notificationMessage,
                    'action_url' => '/messages/' . $conversation->id
                ])
            ]);
            broadcast(new SendMessage($lastCreatedMessage, $sender, $notification, $conversation))->toOthers();
        }

        // === BẮT ĐẦU THAY ĐỔI 2 ===
        // Xử lý upload ảnh (cho cuộc trò chuyện MỚI)
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $captions = $request->input('captions', []);

            // Đường dẫn tương đối
            $relativeSavePath = 'images/client/message';
            // Đường dẫn vật lý (tuyệt đối)
            $destinationPath = public_path($relativeSavePath);

            // Đảm bảo thư mục tồn tại
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            foreach ($images as $index => $image) {
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($image->getMimeType(), $allowedMimes)) {
                    continue;
                }
                
                $filename = time() . '_' . Str::random(10) . '_' . $index . '.' . $image->getClientOriginalExtension();
                
                // Di chuyển tệp vào thư mục public
                $image->move($destinationPath, $filename);

                // Tạo đường dẫn tương đối để lưu vào CSDL
                $path = $relativeSavePath . '/' . $filename;

                $imageMessageData = [
                    'sender_id' => $sender->id,
                    'content' => $captions[$index] ?? '',
                    'message_type' => 'image',
                    'attachment_url' => $path, // Lưu đường dẫn tương đối
                    'sent_at' => now(),
                ];

                $createdImageMessage = $this->conversationRepo->addMessage($conversation->id, $imageMessageData);
                $createdImageMessage->load('sender');

                // ... (Phần notification, broadcast cho ảnh giữ nguyên) ...
                $notificationMessage = $imageMessageData['content'] ? $imageMessageData['content'] : 'Bạn có hình ảnh mới từ ' . $sender->name;
                $notification = Notification::create([
                    'user_id' => $recipientId,
                    'type' => 'message',
                    'reference_id' => $createdImageMessage->id,
                    'reference_type' => 'message',
                    'sender_id' => $sender->id,
                    'message' => $notificationMessage,
                    'created_at' => Carbon::now(),
                    'is_read' => false,
                    'action_url' => '/messages/' . $conversation->id,
                    'data' => json_encode([
                        'message' => $notificationMessage,
                        'action_url' => '/messages/' . $conversation->id
                    ])
                ]);
                broadcast(new SendMessage($createdImageMessage, $sender, $notification, $conversation))->toOthers();

                $lastCreatedMessage = $createdImageMessage;
            }
        }
        // === KẾT THÚC THAY ĐỔI 2 ===


        return response()->json([
            'success' => true,
            'message' => $lastCreatedMessage,
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

    public function markAsRead(Request $request, $conversationId)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($conversationId);

        if (!$conversation->members()->where('users.id', $user->id)->exists()) {
            abort(403, 'Unauthorized');
        }

        // Mark all unread messages as read
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function deleteMessage($messageId)
    {
        $user = Auth::user();
        $message = Message::findOrFail($messageId);

        if ($message->sender_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chỉ có thể xóa tin nhắn của mình!'
            ], 403);
        }

        // Xóa file ảnh nếu có (one attachment_url per DB schema)
        if (in_array($message->message_type, ['image', 'video', 'file']) && $message->attachment_url) {
            Storage::disk('public')->delete($message->attachment_url);
        }

        $message->delete();

        return response()->json(['success' => true]);
    }
}
