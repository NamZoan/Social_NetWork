<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedInteger('likes_count')->default(0)->after('allow_comments');
            $table->unsignedInteger('comments_count')->default(0)->after('likes_count');
            $table->unsignedInteger('shares_count')->default(0)->after('comments_count');
            $table->unsignedInteger('views_count')->default(0)->after('shares_count');

            // Tự đặt tên index để rollback chắc chắn
            $table->index(['likes_count', 'created_at'], 'posts_likes_created_at_idx');
            $table->index(['views_count', 'created_at'], 'posts_views_created_at_idx');
        });

        // Migrate dữ liệu hiện tại (nếu bảng liên quan tồn tại)
        $this->migrateExistingData();
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_likes_created_at_idx');
            $table->dropIndex('posts_views_created_at_idx');

            $table->dropColumn([
                'likes_count',
                'comments_count',
                'shares_count',
                'views_count',
            ]);
        });
    }

    private function migrateExistingData(): void
    {
        // Nếu project bạn có bảng likes dạng polymorphic (content_type, content_id)
        if (Schema::hasTable('likes')) {
            DB::statement("
                UPDATE posts
                SET likes_count = (
                    SELECT COUNT(*)
                    FROM likes
                    WHERE likes.content_type = 'post'
                      AND likes.content_id = posts.id
                )
            ");
        }

        if (Schema::hasTable('comments')) {
            DB::statement("
                UPDATE posts
                SET comments_count = (
                    SELECT COUNT(*)
                    FROM comments
                    WHERE comments.post_id = posts.id
                      AND (comments.is_hidden = 0 OR comments.is_hidden IS NULL)
                )
            ");
        }

        if (Schema::hasTable('user_interactions')) {
            DB::statement("
                UPDATE posts
                SET shares_count = (
                    SELECT COUNT(*)
                    FROM user_interactions
                    WHERE user_interactions.post_id = posts.id
                      AND user_interactions.interaction_type = 'share'
                )
            ");

            DB::statement("
                UPDATE posts
                SET views_count = (
                    SELECT COUNT(DISTINCT user_id)
                    FROM user_interactions
                    WHERE user_interactions.post_id = posts.id
                      AND user_interactions.interaction_type = 'view'
                )
            ");
        }
    }
};
