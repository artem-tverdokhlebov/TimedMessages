<?php

namespace Ridex\TimedMessages\Services;

//use App\TempUser;
use Ridex\TimedMessages\TimedMessage;
use Ridex\TimedMessages\TimedMessageRule;
//use App\User;
use Carbon\Carbon;

class TimedMessagesService {
    public static function run() {
        $timedMessages = TimedMessage::all();

        foreach ($timedMessages as $timedMessage) {
            self::processTimedMessage($timedMessage);
        }
    }

    private static function processTimedMessage(TimedMessage $timedMessage) {
        if ($timedMessage->before_start_date == true && $timedMessage->after_start_date == false) {
            if (strtotime(env('START_DATE')) > time()) { } else {
                return;
            }
        } else if ($timedMessage->before_start_date == false && $timedMessage->after_start_date == true) {
            if (strtotime(env('START_DATE')) <= time()) { } else {
                return;
            }
        }

        $users = [];

        if ($timedMessage->user_type == \TimedMessage\UserType::USER) {
            $users = User::all();
        } elseif ($timedMessage->user_type == \TimedMessage\UserType::TEMP_USER) {
            $users = TempUser::all();
        }

        foreach ($users as $user) {
            $userTimerData = $user->timer_data;

            if (!isset($userTimerData['timed_message_'.$timedMessage->id])) {
                if (\Schema::hasColumn($user->getTable(), $timedMessage->column)) {
                    if ($user->{$timedMessage->column}->addMinutes($timedMessage->minutes) < Carbon::now()) {
                        $rules = $timedMessage->rules()->get();

                        $passedRules = false;
                        foreach ($rules as $rule) {
                            $expression = false;

                            if (\Schema::hasColumn($user->getTable(), $rule->column)) {
                                if (TimedMessageRule::OPERATORS[$rule->operator] == '<') {
                                    $expression = $user->{$rule->column} < $rule->value;
                                } elseif (TimedMessageRule::OPERATORS[$rule->operator] == '<=') {
                                    $expression = $user->{$rule->column} <= $rule->value;
                                } elseif (TimedMessageRule::OPERATORS[$rule->operator] == '=') {
                                    $expression = $user->{$rule->column} == $rule->value;
                                } elseif (TimedMessageRule::OPERATORS[$rule->operator] == '>=') {
                                    $expression = $user->{$rule->column} >= $rule->value;
                                } elseif (TimedMessageRule::OPERATORS[$rule->operator] == '>') {
                                    $expression = $user->{$rule->column} > $rule->value;
                                }
                            }

                            if (!$expression) {
                                $passedRules = false;
                                break;
                            }

                            $passedRules = true;
                        }

                        if ($passedRules) {
                            $userTimerData['timed_message_' . $timedMessage->id] = true;
                            $user->timer_data = $userTimerData;

                            $user->save();

                            $items = $timedMessage->items()->get();

                            foreach ($items as $item) {
                                $item->send($user);
                            }
                        }
                    }
                }
            }
        }
    }
}