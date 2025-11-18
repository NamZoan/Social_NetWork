<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\ConversationRepositoryInterface;
use App\Repositories\ConversationRepository;
use App\Repositories\FriendshipRepositoryInterface;
use App\Repositories\FriendshipRepository;
use Illuminate\Support\Facades\URL;
use App\Repositories\GroupRepositoryInterface;
use App\Repositories\GroupRepository;

use App\Repositories\GroupMemberRepositoryInterface;
use App\Repositories\GroupMemberRepository;

use App\Repositories\ProfileRepositoryInterface;
use App\Repositories\ProfileRepository;

use App\Repositories\PostRepositoryInterface;
use App\Repositories\PostRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ConversationRepositoryInterface::class, ConversationRepository::class);
        $this->app->bind(FriendshipRepositoryInterface::class, FriendshipRepository::class);
        $this->app->bind(ProfileRepositoryInterface::class, ProfileRepository::class);
        $this->app->bind(PostRepositoryInterface::class, PostRepository::class);
        $this->app->bind(GroupRepositoryInterface::class, GroupRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') { // <-- Dòng này là tùy chọn, nhưng nên có
            URL::forceScheme('https'); // <-- THÊM DÒNG NÀY
        }
    }
}
