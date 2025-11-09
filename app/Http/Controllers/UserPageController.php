<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
class UserPageController extends Controller
{
    /**
     * Hiển thị form tạo Page mới
     */
    public function create()
    {
        return Inertia::render('Pages/Create');
    }

    /**
     * Hiển thị trang chi tiết của Page
     */
    public function show($identifier)
    {
        $user = Auth::user();

        // Tìm page theo username hoặc ID
        $page = Page::where('username', $identifier)
            ->orWhere('id', $identifier)
            ->with(['creator', 'admins', 'posts.user'])
            ->firstOrFail();

        // Kiểm tra xem user có đang theo dõi page không
        $isFollowing = $page->isFollowedBy($user->id);

        // Kiểm tra xem user có phải là admin không
        $isAdmin = $page->isAdmin($user->id);
        $adminRole = $isAdmin ? $page->admins()->where('user_id', $user->id)->first()->pivot->role : null;

        // Lấy posts của page
        $posts = $page->posts()
            ->with(['user', 'media', 'likes', 'comments.user'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Pages/Show', [
            'page' => $page,
            'isFollowing' => $isFollowing,
            'isAdmin' => $isAdmin,
            'adminRole' => $adminRole,
            'posts' => $posts,
            'currentUser' => $user,
        ]);
    }

    /**
     * Tạo Page mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:100|unique:pages,username',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // Tạo username tự động nếu không có
        if (empty($validated['username'])) {
            $baseUsername = Str::slug($validated['name'], '');
            $username = $baseUsername;
            $counter = 1;
            while (Page::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }
            $validated['username'] = $username;
        }

        // Xử lý upload ảnh
        // Upload avatar
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');

            $dir = public_path('images/client/pages/profile_pictures'); // -> D:\xampp\htdocs\DoAn\Social_NetWork\public\images\client\pages\profile_pictures
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);

            // Lưu đường dẫn để render ra view (dưới public)
            $validated['profile_picture_url'] = 'images/client/pages/profile_pictures/' . $filename;
        }

        // Upload cover
        if ($request->hasFile('cover_photo')) {
            $file = $request->file('cover_photo');

            $dir = public_path('images/client/pages/cover_photos'); // -> D:\xampp\htdocs\DoAn\Social_NetWork\public\images\client\pages\cover_photos
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);

            $validated['cover_photo_url'] = 'images/client/pages/cover_photos/' . $filename;
        }

        $validated['creator_id'] = $user->id;

        $page = Page::create($validated);

        // Tự động thêm creator làm admin
        $page->admins()->attach($user->id, ['role' => 'admin']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã tạo trang thành công!',
                'page' => $page->load('creator')
            ]);
        }

        return redirect()->route('pages.show', $page->username ?? $page->id)
            ->with('success', 'Đã tạo trang thành công!');
    }

    /**
     * Cập nhật Page
     */
    public function update(Request $request, Page $page)
    {
        $user = Auth::user();

        // Kiểm tra quyền
        if (!$page->isAdmin($user->id)) {
            abort(403, 'Bạn không có quyền chỉnh sửa trang này.');
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'username' => 'nullable|string|max:100|unique:pages,username,' . $page->id,
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Xử lý upload ảnh mới
        if ($request->hasFile('profile_picture')) {
            // Xóa ảnh cũ nếu có (đã lưu dạng 'images/client/pages/profile_pictures/xxx.jpg')
            if (!empty($page->profile_picture_url)) {
                $old = public_path($page->profile_picture_url);
                if (File::exists($old)) {
                    File::delete($old);
                }
            }

            // Thư mục đích: D:\xampp\htdocs\DoAn\Social_NetWork\public\images\client\pages\profile_pictures
            $dir = public_path('images/client/pages/profile_pictures');
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0775, true);
            }

            $file = $request->file('profile_picture');
            $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);

            // Lưu đường dẫn tương đối để dùng asset() khi render
            $validated['profile_picture_url'] = 'images/client/pages/profile_pictures/' . $filename;
        }

        // COVER PHOTO
        if ($request->hasFile('cover_photo')) {
            if (!empty($page->cover_photo_url)) {
                $old = public_path($page->cover_photo_url);
                if (File::exists($old)) {
                    File::delete($old);
                }
            }

            // Thư mục đích: D:\xampp\htdocs\DoAn\Social_NetWork\public\images\client\pages\cover_photos
            $dir = public_path('images/client/pages/cover_photos');
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0775, true);
            }

            $file = $request->file('cover_photo');
            $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);

            $validated['cover_photo_url'] = 'images/client/pages/cover_photos/' . $filename;
        }

        $page->update($validated);

        return back()->with('success', 'Đã cập nhật trang thành công!');
    }

    /**
     * Theo dõi/ Bỏ theo dõi Page
     */
    public function toggleFollow(Request $request, Page $page)
    {
        $user = Auth::user();

        if ($page->isFollowedBy($user->id)) {
            $page->followers()->detach($user->id);
            $page->decrementFollowers();
            $message = 'Đã bỏ theo dõi trang.';
        } else {
            $page->followers()->attach($user->id, [
                'notification_settings' => $request->input('notification_settings', 'all')
            ]);
            $page->incrementFollowers();
            $message = 'Đã theo dõi trang.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'isFollowing' => $page->isFollowedBy($user->id),
            'follower_count' => $page->fresh()->follower_count,
        ]);
    }

    /**
     * Lấy insights của Page (chỉ admin)
     */
    public function insights(Request $request, Page $page)
    {
        $user = Auth::user();

        if (!$page->isAdmin($user->id)) {
            abort(403, 'Bạn không có quyền xem insights.');
        }

        $period = $request->input('period', 30); // 7, 30, 90 days

        // Tính toán insights
        $totalPosts = $page->posts()->count();
        $totalLikes = $page->posts()->withCount('likes')->get()->sum('likes_count');
        $totalComments = $page->posts()->withCount('comments')->get()->sum('comments_count');
        $totalShares = $page->posts()->withCount('shares')->get()->sum('shares_count');

        $postsLastPeriod = $page->posts()
            ->where('created_at', '>=', now()->subDays($period))
            ->count();

        $metrics = [
            [
                'key' => 'reach',
                'label' => 'Lượt tiếp cận',
                'value' => $page->posts()->sum('id') ?? 0, // Placeholder, cần thêm reach field
                'change' => 12.5,
                'icon' => 'bx bx-trending-up',
                'color' => '#1877f2'
            ],
            [
                'key' => 'engagement',
                'label' => 'Lượt tương tác',
                'value' => $totalLikes + $totalComments + $totalShares,
                'change' => 8.3,
                'icon' => 'bx bx-heart',
                'color' => '#f02849'
            ],
            [
                'key' => 'followers',
                'label' => 'Người theo dõi mới',
                'value' => $page->follower_count,
                'change' => 5.2,
                'icon' => 'bx bx-user-plus',
                'color' => '#41b35d'
            ],
            [
                'key' => 'posts',
                'label' => 'Bài đăng',
                'value' => $postsLastPeriod,
                'change' => -2.1,
                'icon' => 'bx bx-file',
                'color' => '#f7b928'
            ],
        ];

        // Top posts
        $topPosts = $page->posts()
            ->with(['user', 'media'])
            ->withCount(['likes', 'comments', 'shares'])
            ->orderByRaw('(SELECT COUNT(*) FROM likes WHERE likes.content_id = posts.id) + (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) DESC')
            ->limit(6)
            ->get();

        $insights = [
            'metrics' => $metrics,
            'total_posts' => $totalPosts,
            'total_followers' => $page->follower_count,
            'engagement_rate' => $this->calculateEngagementRate($page),
            'posts_last_7_days' => $page->posts()
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'posts_last_30_days' => $page->posts()
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'top_posts' => $topPosts,
        ];

        if ($request->expectsJson()) {
            return response()->json($insights);
        }

        return Inertia::render('Pages/Insights', [
            'page' => $page,
            'insights' => $insights,
        ]);
    }

    /**
     * Tính toán tỷ lệ tương tác
     */
    private function calculateEngagementRate(Page $page)
    {
        $totalPosts = $page->posts()->count();
        if ($totalPosts === 0) {
            return 0;
        }

        $totalLikes = $page->posts()->withCount('likes')->get()->sum('likes_count');
        $totalComments = $page->posts()->withCount('comments')->get()->sum('comments_count');
        $totalEngagement = $totalLikes + $totalComments;

        return round(($totalEngagement / ($totalPosts * $page->follower_count)) * 100, 2);
    }

    /**
     * Lấy danh sách posts của page (cho infinite scroll)
     */
    public function getPosts(Request $request, Page $page)
    {
        $posts = $page->posts()
            ->with(['user', 'media', 'likes', 'comments.user'])
            ->latest()
            ->paginate(10);

        return response()->json($posts);
    }
}

