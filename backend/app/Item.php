<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['user_id','name','type','size','parent','absolute_location'];

    public function entries()
    {
        return $this->hasMany(ShareEntry::class, 'item_id')->orderBy('id', 'asc');
    }

}
