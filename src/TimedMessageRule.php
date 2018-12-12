<?php

namespace Ridex\TimedMessages;

use Illuminate\Database\Eloquent\Model;

class TimedMessageRule extends Model
{
    protected $fillable = [
        'column', 'operator', 'value'
    ];

    const COLUMNS = [
        'payment_status' => 'Статус оплаты',
        'utm_source' => 'UTM-метка'
    ];

    const OPERATORS = [
        0 => 'меньше',
        1 => 'меньше или равно',
        2 => 'равно',
        3 => 'больше или равно',
        4 => 'больше'
    ];
}
