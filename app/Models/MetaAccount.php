<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MetaAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_id',
        'account_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'account_name',
        'account_type',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'token_expires_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Mối quan hệ với User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mối quan hệ với Meta Pages
     */
    public function pages()
    {
        return $this->hasMany(MetaPage::class, 'meta_account_id');
    }

    /**
     * Kiểm tra xem token còn hiệu lực không
     */
    public function isTokenValid()
    {
        if (!$this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isFuture();
    }

    /**
     * Lấy token (có thể refresh nếu cần)
     */
    public function getAccessToken()
    {
        if (!$this->isTokenValid() && $this->refresh_token) {
            // TODO: Implement token refresh logic
        }

        return $this->access_token;
    }
}


