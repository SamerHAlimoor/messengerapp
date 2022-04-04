<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['conversation_id', 'user_id', 'body', 'type'];

    // the participats in the conversation
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // the recipients in the Message
    public function recipients()
    {
        //withPivot => to return scpicific columns in recipients
        return $this->belongsToMany(User::class, 'recipients')->withPivot([
            'read_at', 'deleted_at'
        ]);
    }



    // the user who created the conversation
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withDefault(
            [
                'name' => __('User')
            ]
        );
    }
}
