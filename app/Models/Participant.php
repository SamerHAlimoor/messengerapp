<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Participant extends Pivot
{
    use HasFactory;
    //why we will do extend pivot and not Model ,bcz this model consider a pivot model and  pivot extend model so 
    // pivot do  increment false and fillable all column are false 

    public $timestamps =false;
    public $casts=[
        'joined_at'=>'datetime',
    ];


    public function conversaion()
{
    return $this->belongsTo(Conversation::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}
}
