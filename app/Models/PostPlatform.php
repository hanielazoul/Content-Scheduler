<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PostPlatform extends Pivot
{
    protected $fillable = [
        'post_id',
        'platform_id',
        'platform_status',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }
}
