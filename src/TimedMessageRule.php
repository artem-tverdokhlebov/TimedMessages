<?php

namespace Ridex\TimedMessages;

use Illuminate\Database\Eloquent\Model;

class TimedMessageRule extends Model
{
    protected $fillable = [
        'column', 'operator', 'value'
    ];

    const OPERATORS = [
        0 => '<',
        1 => '<=',
        2 => '=',
        3 => '>=',
        4 => '>'
    ];
}
