<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTime;

class Tweet extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'hashtag_id',
        'author_id',
        'name',
        'username',
        'created_at',
        'tweet_id',
        'text'
    ];

    public function hashtag()
    {
        return $this->belongsTo(Hashtag::class);
    }

    public static function correctDateTime($datetime)
    {
        $datetime = new DateTime($datetime);
        return $datetime->format('Y-m-d H:i:s');
    }

}
