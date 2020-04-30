<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

// 分享的"事件"，即一个分享行为
class ShareEvent extends Model
{
    protected $fillable = ['user_id','shared_at','expired_at'];

    public function entries()
    {
        return $this->hasMany(ShareEntry::class, 'share_event_id')->orderBy('id', 'asc');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
