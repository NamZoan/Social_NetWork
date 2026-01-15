<?php
// app/Jobs/UpdateFeedCacheJob.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\FeedController;

class UpdateFeedCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function handle()
    {
        $feedController = new FeedController();
        $feedController->prefetchFeed(new \Illuminate\Http\Request(['user_id' => $this->userId]));
    }
}

// Dispatch job khi user login hoặc thực hiện action
dispatch(new UpdateFeedCacheJob($userId))->delay(now()->addSeconds(5));