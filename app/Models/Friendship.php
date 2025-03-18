<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Friendship extends Model
{
    use HasFactory;

    protected $fillable = ['user_id_1', 'user_id_2', 'status'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id_1');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'user_id_2');
    }
}
