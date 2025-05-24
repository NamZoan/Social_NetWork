<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            Log::info('Fetching notifications for user: ' . Auth::id());

            $notifications = Notification::where('user_id', Auth::id())
                ->whereIn('type', ['comment', 'post'])
                ->where('is_read', 0)
                ->latest()
                ->limit(20)
                ->get();

            Log::info('Found notifications: ' . $notifications->count());

            $formattedNotifications = $notifications->map(function ($notification) {
                try {
                    switch ($notification->type) {
                        case 'reaction':
                            $post = Post::find($notification->reference_id);
                            if (!$post) {
                                Log::warning('Post not found for reaction notification: ' . $notification->id);
                                return null;
                            }
                            return [
                                'id' => $notification->id,
                                'type' => $notification->type,
                                'reaction_type' => $notification->message,
                                'post_id' => $post->id,
                                'post_content' => $post->content,
                                'sender_name' => $notification->sender->name ?? 'Unknown',
                                'sender_avatar' => $notification->sender->avatar,
                                'action_url' => $notification->action_url,
                                'created_at' => $notification->created_at,
                                'is_read' => $notification->is_read
                            ];

                        case 'comment':
                            $post = Post::find($notification->reference_id);
                            if (!$post) {
                                Log::warning('Post not found for comment notification: ' . $notification->id);
                                return null;
                            }
                            return [
                                'id' => $notification->id,
                                'type' => $notification->type,
                                'comment_content' => $notification->message,
                                'post_id' => $post->id,
                                'sender_name' => $notification->sender->name ?? 'Unknown',
                                'sender_avatar' => $notification->sender->avatar,
                                'action_url' => $notification->action_url,
                                'created_at' => $notification->created_at,
                                'is_read' => $notification->is_read
                            ];

                        default:
                            return null;
                    }
                } catch (\Exception $e) {
                    Log::error('Error processing notification ' . $notification->id . ': ' . $e->getMessage());
                    return null;
                }
            })->filter();

            return response()->json([
                'notifications' => $formattedNotifications
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching notifications: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Failed to fetch notifications'], 500);
        }
    }

    public function markAsRead(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notificationId = $request->input('notification_id');
        
        try {
            DB::beginTransaction();

            if ($notificationId) {
                $updated = Notification::where('id', $notificationId)
                    ->where('user_id', Auth::id())
                    ->update(['is_read' => 1]);

                Log::info('Marked notification as read: ' . $notificationId . ', Updated: ' . $updated);
            } else {
                $updated = Notification::where('user_id', Auth::id())
                    ->where('is_read', 0)
                    ->update(['is_read' => 1]);

                Log::info('Marked all notifications as read, Updated: ' . $updated);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error marking notification as read: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Failed to mark notification as read'], 500);
        }
    }

    public function messageNotifications(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            Log::info('Fetching message notifications for user: ' . Auth::id());

            $notifications = Notification::where('user_id', Auth::id())
                ->where('reference_type', 'message')
                ->where('is_read', 0)
                ->latest()
                ->limit(20)
                ->get();

            Log::info('Found message notifications: ' . $notifications->count());

            $formattedNotifications = $notifications->map(function ($notification) {
                try {
                    $message = Message::with(['sender', 'conversation'])
                        ->find($notification->reference_id);
                    if (!$message) {
                        Log::warning('Message not found for notification: ' . $notification->id);
                        return null;
                    }
                    $conversation = $message->conversation;
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'message' => $message->content,
                        'sender_name' => $message->sender->name ?? 'Unknown',
                        'sender_avatar' => $message->sender->avatar,
                        'action_url' => $notification->action_url ?? ($conversation ? "/messages?conversation=" . $conversation->id : '#'),
                        'created_at' => $notification->created_at,
                        'conversation_type' => $conversation ? $conversation->conversation_type : 'individual',
                        'group_name' => $conversation && $conversation->conversation_type === 'group' ? $conversation->name : null,
                        'group_avatar' => $conversation && $conversation->conversation_type === 'group' ? $conversation->image : null,
                        'is_read' => $notification->is_read
                    ];
                } catch (\Exception $e) {
                    Log::error('Error processing message notification ' . $notification->id . ': ' . $e->getMessage());
                    return null;
                }
            })->filter();



            return response()->json([
                'notifications' => $formattedNotifications
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching message notifications: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Failed to fetch message notifications'], 500);
        }
    }

    public function markAllAsRead(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            DB::beginTransaction();

            Notification::where('user_id', Auth::id())
                ->where('is_read', 0)
                ->update(['is_read' => 1]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error marking all notifications as read: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Failed to mark all notifications as read'], 500);
        }
    }

    public function indexNotification(Request $request)
{
    try {
        $notifications = Notification::where('user_id', Auth::id())
            ->whereIn('type', ['comment', 'post', 'reaction'])
            ->latest()
            ->paginate(10);

        $formattedNotifications = $notifications->getCollection()->map(function ($notification) {
            try {
                switch ($notification->type) {
                    case 'reaction':
                        $post = Post::find($notification->reference_id);
                        if (!$post) {
                            Log::warning('Post not found for reaction notification: ' . $notification->id);
                            return null;
                        }
                        return [
                            'id' => $notification->id,
                            'type' => $notification->type,
                            'reaction_type' => $notification->message,
                            'post_id' => $post->id,
                            'post_content' => $post->content,
                            'sender_name' => $notification->sender->name ?? 'Unknown',
                            'sender_avatar' => $notification->sender->avatar,
                            'action_url' => $notification->action_url,
                            'created_at' => $notification->created_at,
                            'is_read' => $notification->is_read
                        ];

                    case 'comment':
                        $post = Post::find($notification->reference_id);
                        if (!$post) {
                            Log::warning('Post not found for comment notification: ' . $notification->id);
                            return null;
                        }
                        return [
                            'id' => $notification->id,
                            'type' => $notification->type,
                            'comment_content' => $notification->message,
                            'post_id' => $post->id,
                            'sender_name' => $notification->sender->name ?? 'Unknown',
                            'sender_avatar' => $notification->sender->avatar,
                            'action_url' => $notification->action_url,
                            'created_at' => $notification->created_at,
                            'is_read' => $notification->is_read
                        ];

                    default:
                        return null;
                }
            } catch (\Exception $e) {
                Log::error('Error processing notification ' . $notification->id . ': ' . $e->getMessage());
                return null;
            }
        })->filter()->values();

        // Gán lại collection đã xử lý vào paginator
        $notifications->setCollection($formattedNotifications);

        return Inertia::render('Notification/Index', [
            'notifications' => $notifications
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching notifications: ' . $e->getMessage());
        return back()->with('error', 'Failed to fetch notifications');
    }
}

}
