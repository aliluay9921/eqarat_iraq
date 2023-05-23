<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory, Uuids;

    protected $guarded = [];

    protected $casts = [
        'expaired_offer' => 'datetime',
    ];

    protected $with = ["images", "user"];



    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function images()
    {
        return $this->hasMany(Image::class, "target_id");
    }
}
