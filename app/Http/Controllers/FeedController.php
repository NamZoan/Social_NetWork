<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Models\Post;
use App\Models\Group;
use App\Models\Page;
use App\Models\UserInteraction;
use Inertia\Inertia;

class FeedController extends Controller
{
    const INTERACTION_WEIGHTS = [
        'view' => 1,
        'like' => 5,
        'comment' => 7,
        'share' => 10,
    ];

    /**
     * Trang chủ - Load initial feed
     */
    public function index()
    {
        $user = Auth::user();

        // Warm up cache khi user vào trang chủ
        $this->warmUpCache($user->id);

        $friendIds = $this->getFriendIds($user->id);
        $followedPageIds = $this->getFollowedPageIds($user->id);
        $groupIds = $this->getUserGroupIds($user->id);

        $initialData = $this->getPersonalizedFeed(
            $user->id,
            $friendIds,
            $followedPageIds,
            $groupIds,
            null,
            15,
            []
        );

        $sidebar = [
            'suggestedFriends' => $this->getSuggestedFriends($user->id, $friendIds, 5),
            'suggestedGroups' => $this->getSuggestedGroups($user->id, 3),
            'suggestedPages' => $this->getSuggestedPages($user->id, 3),
            'contacts' => [],
            'notifications' => [],
        ];
        return Inertia::render('Home', [
            'initialPosts' => $initialData['data'],
            'nextCursor' => $initialData['next_cursor'],
            'hasMore' => $initialData['has_more'],
            'user' => $user,
            'sidebar' => $sidebar,
        ]);
    }

    /**
     * Suggested posts similar to recent like/share interactions.
     */
    private function getSuggestedSimilarPosts($userId, $friendIds, $followedPageIds, $groupIds, $limit = 3, $excludeIds = [])
    {
        if (!Schema::hasTable('related_posts')) {
            return collect();
        }

        $recentInteractionPostIds = UserInteraction::where('user_id', $userId)
            ->whereIn('interaction_type', ['like', 'share', 'comment', 'view'])
            ->limit(10)
            ->pluck('post_id')
            ->toArray();

        if (empty($recentInteractionPostIds)) {
            return collect();
        }

        $relatedScores = DB::table('related_posts')
            ->select('related_post_id', DB::raw('MAX(similarity_score) as score'))
            ->whereIn('post_id', $recentInteractionPostIds)
            ->groupBy('related_post_id')
            ->orderByDesc('score')
            ->limit($limit * 3)
            ->get();

        if ($relatedScores->isEmpty()) {
            return collect();
        }

        $relatedIds = $relatedScores->pluck('related_post_id')->toArray();
        $scoresById = $relatedScores->pluck('score', 'related_post_id')->toArray();
        $excludeIds = array_merge($excludeIds, $recentInteractionPostIds);

        $posts = Post::with([
            'user:id,name,username,avatar',
            'page:id,name,username,profile_picture_url',
            'group:id,name,cover_photo_url',
            'media',
            'likes' => fn($q) => $q->where('user_id', $userId)->select('id', 'content_id', 'user_id'),
        ])
            ->select('posts.*')
            ->whereIn('posts.id', $relatedIds)
            ->whereNotIn('posts.id', $excludeIds)
            ->where(function ($query) use ($userId, $friendIds, $followedPageIds, $groupIds) {
                $this->applyFeedFilters($query, $userId, $friendIds, $followedPageIds, $groupIds);
            })
            ->where(function ($query) use ($userId) {
                $this->excludeInteractedPosts($query, $userId);
            })
            ->get();

        if ($posts->isEmpty()) {
            return collect();
        }

        $posts = $posts->sortByDesc(function ($post) use ($scoresById) {
            return $scoresById[$post->id] ?? 0;
        })->values()->take($limit);

        $posts->each(function ($post) use ($userId) {
            $post->is_liked = $post->likes->isNotEmpty();
            $post->is_suggested = true;
            $post->suggested_reason = 'similar';
            unset($post->likes);
        });

        return $posts;
    }

    /**
     * API: Lấy newsfeed với cursor pagination
     */
    public function getNewsFeed(Request $request)
    {
        $userId = Auth::id();
        $cursor = $request->get('cursor');
        $limit = $request->get('per_page', 5);
        $seenPostIds = $request->get('seen_posts', []);

        $friendIds = $this->getFriendIds($userId);
        $followedPageIds = $this->getFollowedPageIds($userId);
        $groupIds = $this->getUserGroupIds($userId);

        $posts = $this->getPersonalizedFeed(
            $userId,
            $friendIds,
            $followedPageIds,
            $groupIds,
            $cursor,
            $limit,
            $seenPostIds
        );

        return response()->json([
            'success' => true,
            'data' => $posts['data'],
            'next_cursor' => $posts['next_cursor'],
            'has_more' => $posts['has_more'],
            'feed_type' => $posts['feed_type'],
        ]);
    }

    /**
     * Feed scoring algorithm giống Facebook
     */
    private function getPersonalizedFeed($userId, $friendIds, $followedPageIds, $groupIds, $cursor = null, $limit = 5, $excludeIds = [])
    {
        $query = Post::with([
            'user:id,name,username,avatar',
            'page:id,name,username,profile_picture_url',
            'group:id,name,cover_photo_url',
            'media',
            'likes' => fn($q) => $q->where('user_id', $userId)->select('id', 'content_id', 'user_id'),
        ])
            ->withCount(['comments', 'likes'])
            ->select('posts.*')
            ->selectRaw($this->getFeedScoreQuery($userId, $friendIds, $followedPageIds))
            ->where(function ($query) use ($userId, $friendIds, $followedPageIds, $groupIds) {
                $this->applyFeedFilters($query, $userId, $friendIds, $followedPageIds, $groupIds);
            })
            ->where(function ($query) use ($userId) {
                $this->excludeInteractedPosts($query, $userId);
            })
            ->whereNotIn('posts.id', $excludeIds);


        $posts = $query->orderByDesc('feed_score')

            ->limit($limit + 1)
            ->get();

        $hasMore = $posts->count() > $limit;

        if ($hasMore) {
            $posts = $posts->slice(0, $limit);
        }

        $nextCursor = $hasMore && $posts->isNotEmpty()
            ? $posts->last()->created_at->toIso8601String()
            : null;

        $posts = $posts->map(function ($post) use ($userId) {
            $post->is_liked = $post->likes->isNotEmpty();
            unset($post->likes);
            return $post;
        });

        // Đảm bảo diversity
        $posts = $this->ensureFeedDiversity($posts, $userId, $limit);

        $feedType = $this->determineFeedType($posts);

        if ($cursor) {
            $excludeWithCurrent = array_merge($excludeIds, $posts->pluck('id')->toArray());
            $suggestedPosts = $this->getSuggestedSimilarPosts(
                $userId,
                $friendIds,
                $followedPageIds,
                $groupIds,
                3,
                $excludeWithCurrent
            );

            if ($suggestedPosts->isNotEmpty()) {
                $posts = $posts->concat($suggestedPosts);
            }
        }

        return [
            'data' => $posts->values(),
            'next_cursor' => $nextCursor,
            'has_more' => $hasMore,
            'feed_type' => $feedType,
        ];
    }

    /**
     * Query tính điểm feed phức tạp như Facebook
     */
    private function getFeedScoreQuery($userId, $friendIds, $followedPageIds)
    {
        $friendIdsStr = !empty($friendIds) ? implode(',', $friendIds) : '0';
        $pageIdsStr = !empty($followedPageIds) ? implode(',', $followedPageIds) : '0';

        return "
            (
                -- 1. RELATIONSHIP SCORE (0-100 điểm)
                CASE 
                    WHEN posts.user_id IN ({$friendIdsStr}) THEN 80
                    WHEN posts.page_id IN ({$pageIdsStr}) THEN 60
                    ELSE 20
                END
                
                + -- 2. INTERACTION HISTORY SCORE (0-100 điểm)
                LEAST(COALESCE((
                    SELECT SUM(
                        CASE interaction_type
                            WHEN 'like' THEN 5
                            WHEN 'comment' THEN 7
                            WHEN 'share' THEN 10
                            WHEN 'view' THEN 1
                            ELSE 0
                        END
                    )
                    FROM user_interactions
                    WHERE user_interactions.user_id = {$userId}
                    AND user_interactions.post_id = posts.id
                ), 0), 100)
                
                + -- 3. AUTHOR AFFINITY SCORE (0-80 điểm)
                LEAST(COALESCE((
                    SELECT COUNT(*) * 2
                    FROM user_interactions ui
                    JOIN posts p ON ui.post_id = p.id
                    WHERE ui.user_id = {$userId}
                    AND p.user_id = posts.user_id
                    AND ui.interaction_type IN ('like', 'comment', 'share')
                ), 0), 80)
                

                
                + -- 5. ENGAGEMENT SCORE (0-100 điểm)
                LEAST((
                    (SELECT COUNT(*) FROM likes WHERE content_type = 'post' AND content_id = posts.id) * 2
                    + (SELECT COUNT(*) FROM comments WHERE post_id = posts.id) * 5
                    + (SELECT COUNT(*) FROM user_interactions WHERE post_id = posts.id AND interaction_type = 'share') * 10
                ), 100)
                
                
                
                + -- 8. GROUP ACTIVITY BONUS (0-40 điểm)
                CASE
                    WHEN posts.group_id IS NOT NULL THEN
                        LEAST(COALESCE((
                            SELECT COUNT(*) * 4
                            FROM user_interactions ui
                            JOIN posts p ON ui.post_id = p.id
                            WHERE ui.user_id = {$userId}
                            AND p.group_id = posts.group_id
                        ), 0), 40)
                    ELSE 0
                END
                
                + -- 9. DIVERSITY PENALTY
                -1 * LEAST(COALESCE((
                    SELECT COUNT(*)
                    FROM user_interactions
                    WHERE user_id = {$userId}
                    AND post_id IN (
                        SELECT id FROM posts p2 WHERE p2.user_id = posts.user_id
                    )
                    AND interaction_type = 'view'
                ), 0) * 5, 30)
                
            ) as feed_score
        ";
    }

    /**
     * Apply filters cho feed
     */
    private function applyFeedFilters($query, $userId, $friendIds, $followedPageIds, $groupIds)
    {
        $query->where(function ($q) use ($userId, $friendIds, $followedPageIds, $groupIds) {
            $q->where('posts.user_id', $userId);

            if (!empty($friendIds)) {
                $q->orWhere(function ($subQ) use ($friendIds) {
                    $subQ->whereIn('posts.user_id', $friendIds)
                        ->whereNull('posts.page_id')
                        ->whereIn('posts.privacy_setting', ['public', 'friends']);
                });
            }

            if (!empty($followedPageIds)) {
                $q->orWhere(function ($subQ) use ($followedPageIds) {
                    $subQ->whereIn('posts.page_id', $followedPageIds)
                        ->where('posts.privacy_setting', 'public');
                });
            }

            if (!empty($groupIds)) {
                $q->orWhere(function ($subQ) use ($groupIds) {
                    $subQ->whereIn('posts.group_id', $groupIds)
                        ->whereNotIn('posts.privacy_setting', ['pending', 'rejected']);
                });
            }

            $q->orWhere(function ($subQ) {
                $subQ->where('posts.privacy_setting', 'public')
                    ->where(function ($q2) {
                        $q2->whereNotNull('posts.page_id')
                            ->orWhereNotNull('posts.group_id');
                    });
            });
        });
    }

    /**
     * Exclude posts the user has already viewed/liked/shared.
     */
    private function excludeInteractedPosts($query, $userId)
    {
        $query->whereNotExists(function ($subQuery) use ($userId) {
            $subQuery->select(DB::raw(1))
                ->from('user_interactions')
                ->whereColumn('user_interactions.post_id', 'posts.id')
                ->where('user_interactions.user_id', $userId)
                ->whereIn('user_interactions.interaction_type', ['view', 'like', 'share']);
        });
    }

    private function getSuggestedFriends($userId, $friendIds = [], $limit = 5)
    {
        $friendIdsStr = !empty($friendIds) ? implode(',', $friendIds) : '0';

        return DB::table('users')
            ->select('users.id', 'users.name', 'users.username', 'users.avatar')
            ->selectRaw("(
                SELECT COUNT(*)
                FROM friendships f
                WHERE f.status = 'accepted'
                AND (
                    (f.user_id_1 = users.id AND f.user_id_2 IN ({$friendIdsStr}))
                    OR (f.user_id_2 = users.id AND f.user_id_1 IN ({$friendIdsStr}))
                )
            ) as mutual_count")
            ->where('users.id', '!=', $userId)
            ->when(!empty($friendIds), function ($query) use ($friendIds) {
                $query->whereNotIn('users.id', $friendIds);
            })
            ->whereNotExists(function ($subQuery) use ($userId) {
                $subQuery->select(DB::raw(1))
                    ->from('friendships')
                    ->where(function ($q) use ($userId) {
                        $q->where(function ($inner) use ($userId) {
                            $inner->where('friendships.user_id_1', $userId)
                                ->whereColumn('friendships.user_id_2', 'users.id');
                        })->orWhere(function ($inner) use ($userId) {
                            $inner->where('friendships.user_id_2', $userId)
                                ->whereColumn('friendships.user_id_1', 'users.id');
                        });
                    });
            })
            ->orderByDesc('mutual_count')
            ->orderBy('users.name')
            ->limit($limit)
            ->get();
    }

    private function getSuggestedGroups($userId, $limit = 3)
    {
        return Group::query()
            ->select('groups.id', 'groups.name', 'groups.description', 'groups.cover_photo_url', 'groups.group_type', 'groups.privacy_setting')
            ->withCount(['members as active_members_count' => function ($query) {
                $query->where('membership_status', 'active');
            }])
            ->whereNotExists(function ($subQuery) use ($userId) {
                $subQuery->select(DB::raw(1))
                    ->from('group_members')
                    ->whereColumn('group_members.group_id', 'groups.id')
                    ->where('group_members.user_id', $userId);
            })
            ->orderByDesc('active_members_count')
            ->limit($limit)
            ->get();
    }

    private function getSuggestedPages($userId, $limit = 3)
    {
        return Page::query()
            ->select('pages.id', 'pages.name', 'pages.username', 'pages.profile_picture_url', 'pages.category', 'pages.follower_count')
            ->where('pages.is_active', true)
            ->where('pages.creator_id', '!=', $userId)
            ->whereNotExists(function ($subQuery) use ($userId) {
                $subQuery->select(DB::raw(1))
                    ->from('page_followers')
                    ->whereColumn('page_followers.page_id', 'pages.id')
                    ->where('page_followers.user_id', $userId);
            })
            ->whereNotExists(function ($subQuery) use ($userId) {
                $subQuery->select(DB::raw(1))
                    ->from('page_admins')
                    ->whereColumn('page_admins.page_id', 'pages.id')
                    ->where('page_admins.user_id', $userId);
            })
            ->orderByDesc('pages.follower_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Đảm bảo feed đa dạng
     */
    private function ensureFeedDiversity($posts, $userId, $limit = 5)
    {
        $diversePosts = collect();
        $authorCounts = [];
        $maxPostsPerAuthor = 3;

        foreach ($posts as $post) {
            $authorId = $post->user_id ?? $post->page_id ?? 'unknown';

            if (!isset($authorCounts[$authorId])) {
                $authorCounts[$authorId] = 0;
            }

            if ($authorCounts[$authorId] < $maxPostsPerAuthor) {
                $diversePosts->push($post);
                $authorCounts[$authorId]++;
            }

            if ($diversePosts->count() >= $limit) {
                break;
            }
        }

        if ($diversePosts->count() < $limit) {
            $needed = $limit - $diversePosts->count();
            $additionalPosts = $this->getSmartRecommendedPosts(
                $userId,
                $needed,
                $diversePosts->pluck('id')->toArray()
            );
            $diversePosts = $diversePosts->merge($additionalPosts);
        }

        return $diversePosts;
    }

    /**
     * Lấy bài viết recommended thông minh
     */
    private function getSmartRecommendedPosts($userId, $limit = 5, $excludeIds = [])
    {
        $recentInteractions = UserInteraction::where('user_id', $userId)
            ->whereIn('interaction_type', ['like', 'comment', 'share', 'click'])
            ->limit(5)
            ->pluck('post_id')
            ->toArray();

        if (empty($recentInteractions)) {
            return collect();
        }

        $relatedPostIds = DB::table('related_posts')
            ->whereIn('post_id', $recentInteractions)
            ->orderBy('similarity_score', 'desc')
            ->limit($limit * 3)
            ->pluck('related_post_id')
            ->toArray();

        $preferredAuthorIds = $this->getPreferredAuthors($userId, 5);

        $posts = Post::with(['user', 'page', 'group', 'media'])
            ->withCount(['comments', 'likes'])
            ->select('posts.*')
            ->selectRaw('
                CASE 
                    WHEN posts.user_id IN (' . implode(',', array_merge($preferredAuthorIds, [0])) . ') THEN 50
                    ELSE 0
                END +
                CASE 
                    WHEN posts.id IN (' . implode(',', array_merge($relatedPostIds, [0])) . ') THEN 30
                    ELSE 0
                END +
                (SELECT COUNT(*) FROM likes WHERE likes.content_id = posts.id AND likes.content_type = "post") * 2 +
                (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) * 3
                as recommendation_score
            ')
            ->where('privacy_setting', 'public')
            ->whereNotIn('id', $excludeIds)
            ->where(function ($query) use ($userId) {
                $this->excludeInteractedPosts($query, $userId);
            })
            ->orderBy('recommendation_score', 'desc')
            ->limit($limit)
            ->get();

        return $posts;
    }

    /**
     * Lấy tác giả user hay tương tác
     */
    private function getPreferredAuthors($userId, $limit = 10)
    {
        $cacheKey = "preferred_authors_{$userId}";

        return Cache::remember($cacheKey, 3600, function () use ($userId, $limit) {
            return DB::table('user_interactions')
                ->join('posts', 'user_interactions.post_id', '=', 'posts.id')
                ->where('user_interactions.user_id', $userId)
                ->whereIn('user_interactions.interaction_type', ['like', 'comment', 'share'])
                ->select('posts.user_id')
                ->selectRaw('COUNT(*) as interaction_count')
                ->groupBy('posts.user_id')
                ->orderBy('interaction_count', 'desc')
                ->limit($limit)
                ->pluck('user_id')
                ->toArray();
        });
    }

    /**
     * Ghi nhận tương tác
     */
    public function trackInteraction(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'interaction_type' => 'required|in:view,click,like,share,comment',
        ]);

        UserInteraction::create([
            'user_id' => Auth::id(),
            'post_id' => $validated['post_id'],
            'interaction_type' => $validated['interaction_type'],
        ]);

        Cache::forget("preferred_authors_" . Auth::id());
        Cache::forget("recommended_posts_" . Auth::id());

        return response()->json(['success' => true]);
    }

    /**
     * Prefetch feed
     */
    public function prefetchFeed(Request $request)
    {
        $userId = Auth::id();
        $cacheKey = "prefetch_feed_{$userId}";

        $posts = Cache::remember($cacheKey, 300, function () use ($userId) {
            $friendIds = $this->getFriendIds($userId);
            $followedPageIds = $this->getFollowedPageIds($userId);
            $groupIds = $this->getUserGroupIds($userId);

            return $this->getPersonalizedFeed(
                $userId,
                $friendIds,
                $followedPageIds,
                $groupIds,
                null,
                30,
                []
            );
        });

        return response()->json($posts);
    }

    /**
     * Warm up cache
     */
    public function warmUpCache($userId)
    {
        dispatch(function () use ($userId) {
            $this->getFriendIds($userId);
            $this->getFollowedPageIds($userId);
            $this->getUserGroupIds($userId);
            $this->getPreferredAuthors($userId);
        })->afterResponse();
    }

    /**
     * Helper: Lấy friend IDs
     */
    private function getFriendIds($userId)
    {
        return Cache::remember("friend_ids_{$userId}", 3600, function () use ($userId) {
            return DB::table('friendships')
                ->where(function ($query) use ($userId) {
                    $query->where('user_id_1', $userId)
                        ->orWhere('user_id_2', $userId);
                })
                ->where('status', 'accepted')
                ->get()
                ->map(function ($friendship) use ($userId) {
                    return $friendship->user_id_1 == $userId
                        ? $friendship->user_id_2
                        : $friendship->user_id_1;
                })
                ->toArray();
        });
    }

    /**
     * Helper: Lấy followed page IDs
     */
    private function getFollowedPageIds($userId)
    {
        return Cache::remember("followed_pages_{$userId}", 3600, function () use ($userId) {
            return DB::table('page_followers')
                ->where('user_id', $userId)
                ->pluck('page_id')
                ->toArray();
        });
    }

    /**
     * Helper: Lấy group IDs
     */
    private function getUserGroupIds($userId)
    {
        return Cache::remember("user_groups_{$userId}", 3600, function () use ($userId) {
            return DB::table('group_members')
                ->where('user_id', $userId)
                ->where('membership_status', 'active')
                ->pluck('group_id')
                ->toArray();
        });
    }

    /**
     * Xác định loại feed
     */
    private function determineFeedType($posts)
    {
        if ($posts->isEmpty()) {
            return 'empty';
        }

        $friendsPosts = $posts->filter(fn($p) => $p->user_id && !$p->page_id && !$p->group_id)->count();
        $total = $posts->count();

        if ($friendsPosts / $total > 0.7) {
            return 'friends';
        } elseif ($friendsPosts / $total < 0.3) {
            return 'recommended';
        }

        return 'mixed';
    }

    /**
     * Check for new posts
     */
    public function checkNewPosts(Request $request)
    {
        $userId = Auth::id();
        $since = $request->get('since');

        if (!$since) {
            return response()->json(['count' => 0]);
        }

        $friendIds = $this->getFriendIds($userId);
        $followedPageIds = $this->getFollowedPageIds($userId);
        $groupIds = $this->getUserGroupIds($userId);

        $count = Post::where(function ($query) use ($userId, $friendIds, $followedPageIds, $groupIds) {
            $this->applyFeedFilters($query, $userId, $friendIds, $followedPageIds, $groupIds);
        })
            ->count();

        return response()->json(['count' => $count]);
    }
}
