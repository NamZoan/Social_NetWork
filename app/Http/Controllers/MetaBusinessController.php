<?php

namespace App\Http\Controllers;

use App\Models\MetaAccount;
use App\Models\MetaPage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaBusinessController extends Controller
{
    /**
     * Hiển thị danh sách Meta Accounts của user
     */
    public function index()
    {
        $user = Auth::user();
        $accounts = $user->metaAccounts()->with('pages')->get();

        return Inertia::render('MetaBusiness/Index', [
            'accounts' => $accounts,
        ]);
    }

    /**
     * Hiển thị form kết nối Meta Business Account
     */
    public function create()
    {
        // Meta Business Suite OAuth URL
        $clientId = config('services.meta.client_id');
        $redirectUri = route('meta.callback');
        $scope = 'business_management,pages_read_engagement,pages_show_list,pages_manage_metadata';

        $authUrl = "https://www.facebook.com/v18.0/dialog/oauth?" . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
            'response_type' => 'code',
        ]);

        return Inertia::render('MetaBusiness/Connect', [
            'authUrl' => $authUrl,
        ]);
    }

    /**
     * Xử lý callback từ Meta OAuth
     */
    public function callback(Request $request)
    {
        $code = $request->get('code');

        if (!$code) {
            return redirect()->route('meta.index')
                ->with('error', 'Không thể kết nối với Meta Business Suite.');
        }

        try {
            // Exchange code for access token
            $tokenResponse = Http::post('https://graph.facebook.com/v18.0/oauth/access_token', [
                'client_id' => config('services.meta.client_id'),
                'client_secret' => config('services.meta.client_secret'),
                'redirect_uri' => route('meta.callback'),
                'code' => $code,
            ]);

            if (!$tokenResponse->successful()) {
                throw new \Exception('Không thể lấy access token.');
            }

            $tokenData = $tokenResponse->json();
            $accessToken = $tokenData['access_token'];
            $expiresIn = $tokenData['expires_in'] ?? null;

            // Lấy thông tin user từ Meta
            $userResponse = Http::get('https://graph.facebook.com/v18.0/me', [
                'access_token' => $accessToken,
                'fields' => 'id,name',
            ]);

            if (!$userResponse->successful()) {
                throw new \Exception('Không thể lấy thông tin user.');
            }

            $userData = $userResponse->json();

            // Lấy danh sách business accounts
            $businessResponse = Http::get('https://graph.facebook.com/v18.0/me/businesses', [
                'access_token' => $accessToken,
            ]);

            $businesses = $businessResponse->successful() ? $businessResponse->json()['data'] ?? [] : [];

            // Tạo hoặc cập nhật Meta Account
            $metaAccount = MetaAccount::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'account_id' => $userData['id'],
                ],
                [
                    'access_token' => $accessToken,
                    'token_expires_at' => $expiresIn ? now()->addSeconds($expiresIn) : null,
                    'account_name' => $userData['name'] ?? 'Meta Account',
                    'account_type' => 'business',
                    'is_active' => true,
                ]
            );

            // Lấy danh sách pages
            $this->syncPages($metaAccount, $accessToken);

            return redirect()->route('meta.index')
                ->with('success', 'Đã kết nối với Meta Business Suite thành công.');
        } catch (\Exception $e) {
            Log::error('Meta Business OAuth Error: ' . $e->getMessage());
            return redirect()->route('meta.index')
                ->with('error', 'Có lỗi xảy ra khi kết nối: ' . $e->getMessage());
        }
    }

    /**
     * Đồng bộ danh sách pages từ Meta API
     */
    private function syncPages(MetaAccount $account, $accessToken = null)
    {
        try {
            $accessToken = $accessToken ?? $account->getAccessToken();

            if (!$accessToken) {
                return false;
            }

            // Lấy danh sách pages
            $pagesResponse = Http::get('https://graph.facebook.com/v18.0/me/accounts', [
                'access_token' => $accessToken,
                'fields' => 'id,name,about,category,cover,picture,fan_count,followers_count',
            ]);

            if (!$pagesResponse->successful()) {
                throw new \Exception('Không thể lấy danh sách pages.');
            }

            $pages = $pagesResponse->json()['data'] ?? [];

            foreach ($pages as $pageData) {
                // Lấy page access token
                $pageTokenResponse = Http::get("https://graph.facebook.com/v18.0/{$pageData['id']}", [
                    'access_token' => $accessToken,
                    'fields' => 'access_token',
                ]);

                $pageToken = $pageTokenResponse->successful()
                    ? $pageTokenResponse->json()['access_token'] ?? null
                    : null;

                MetaPage::updateOrCreate(
                    [
                        'meta_account_id' => $account->id,
                        'page_id' => $pageData['id'],
                    ],
                    [
                        'page_name' => $pageData['name'] ?? 'Untitled Page',
                        'page_description' => $pageData['about'] ?? null,
                        'page_category' => $pageData['category'] ?? null,
                        'page_access_token' => $pageToken,
                        'cover_photo_url' => $pageData['cover']['source'] ?? null,
                        'profile_picture_url' => $pageData['picture']['data']['url'] ?? null,
                        'followers_count' => $pageData['followers_count'] ?? 0,
                        'likes_count' => $pageData['fan_count'] ?? 0,
                        'is_active' => true,
                        'is_connected' => true,
                        'last_synced_at' => now(),
                    ]
                );
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Sync Pages Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Hiển thị chi tiết Meta Account và danh sách pages
     */
    public function show(MetaAccount $account)
    {
        if ($account->user_id !== Auth::id()) {
            abort(403);
        }

        $account->load('pages');

        return Inertia::render('MetaBusiness/Show', [
            'account' => $account,
        ]);
    }

    /**
     * Đồng bộ lại danh sách pages
     */
    public function sync(MetaAccount $account)
    {
        if ($account->user_id !== Auth::id()) {
            abort(403);
        }

        $result = $this->syncPages($account, null);

        if ($result) {
            return back()->with('success', 'Đã đồng bộ danh sách pages thành công.');
        }

        return back()->with('error', 'Có lỗi xảy ra khi đồng bộ pages.');
    }

    /**
     * Ngắt kết nối Meta Account
     */
    public function disconnect(MetaAccount $account)
    {
        if ($account->user_id !== Auth::id()) {
            abort(403);
        }

        $account->update([
            'is_active' => false,
            'access_token' => null,
        ]);

        $account->pages()->update([
            'is_connected' => false,
        ]);

        return back()->with('success', 'Đã ngắt kết nối Meta Account.');
    }

    /**
     * Lấy insights của một page
     */
    public function getPageInsights(MetaPage $page)
    {
        if ($page->metaAccount->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            $accessToken = $page->page_access_token;

            if (!$accessToken) {
                return response()->json(['error' => 'Page access token không tồn tại.'], 400);
            }

            // Lấy insights (7 ngày gần nhất)
            $insightsResponse = Http::get("https://graph.facebook.com/v18.0/{$page->page_id}/insights", [
                'access_token' => $accessToken,
                'metric' => 'page_fans,page_engaged_users,page_impressions,page_reach',
                'period' => 'day',
                'since' => now()->subDays(7)->timestamp,
                'until' => now()->timestamp,
            ]);

            if ($insightsResponse->successful()) {
                $insights = $insightsResponse->json();
                $page->updateInsights($insights);

                return response()->json([
                    'success' => true,
                    'insights' => $insights,
                ]);
            }

            return response()->json(['error' => 'Không thể lấy insights.'], 400);
        } catch (\Exception $e) {
            Log::error('Get Page Insights Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

