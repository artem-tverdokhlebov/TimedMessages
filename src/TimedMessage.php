<?php

namespace Ridex\TimedMessages;

use Illuminate\Database\Eloquent\Model;

class TimedMessage extends Model
{
    const COLUMNS = [
        'created_at' => 'Дата создания',
        'updated_at' => 'Дата изменения',
        'paid_at' => 'Дата оплаты'
    ];

    public function rules() {
        return $this->hasMany('Ridex\TimedMessages\TimedMessageRule');
    }

    public function items() {
        return $this->hasMany('Ridex\TimedMessages\TimedMessageItem');
    }
}

namespace Ridex\TimedMessages\TimedMessage;

class UserType {
    const USER = 0;
    const TEMP_USER = 1;

    public static function toArray() {
        return [
            self::USER => 'Пользователь',
            self::TEMP_USER => 'Временный пользователь'
        ];
    }
}

class TimeType {
    const BEFORE_START_DATE = 0;
    const AFTER_START_DATE = 1;

    public static function toArray() {
        return [
            self::BEFORE_START_DATE => 'До начала марафона',
            self::AFTER_START_DATE => 'После начала марафона'
        ];
    }
}

class TimedMessageType {
    const TELEGRAM = 0;
    const EMAIL = 1;

    public static function toArray() {
        return [
            self::TELEGRAM => 'Telegram',
            self::EMAIL => 'E-mail'
        ];
    }
}