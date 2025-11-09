<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'meta_account_id',
        'page_id',
        'page_name',
        'page_description',
        'page_category',
        'page_access_token',
        'cover_photo_url',
        'profile_picture_url',
        'followers_count',
        'likes_count',
        'insights',
        'settings',
        'is_active',
        'is_connected',
        'last_synced_at',
    ];

    protected $casts = [
        'insights' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_connected' => 'boolean',
        'last_synced_at' => 'datetime',
        'followers_count' => 'integer',
        'likes_count' => 'integer',
    ];

    /**
     * Mối quan hệ với MetaAccount
     */
    public function metaAccount()
    {
        return $this->belongsTo(MetaAccount::class, 'meta_account_id');
    }

    /**
     * Lấy URL trang Facebook
     */
    public function getFacebookUrlAttribute()
    {
        return "https://www.facebook.com/{$this->page_id}";
    }

    /**
     * Cập nhật insights từ Meta API
     */
    public function updateInsights($insights)
    {
        $this->insights = $insights;
        $this->last_synced_at = now();
        $this->save();
    }
}


