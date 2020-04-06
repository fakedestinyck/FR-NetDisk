<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShareEntry extends Model
{
    protected $fillable = ['item_id','share_event_id','link'];

    public function events()
    {
        return $this->belongsTo(ShareEvent::class, 'share_entry_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
