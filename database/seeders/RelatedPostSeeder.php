<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RelatedPost;

class RelatedPostSeeder extends Seeder
{
    public function run()
    {
        RelatedPost::create([
            'post_id' => 1,
            'related_post_id' => 2,
            'similarity_score' => 0.87
        ]);

        RelatedPost::create([
            'post_id' => 1,
            'related_post_id' => 3,
            'similarity_score' => 0.74
        ]);
    }
}
