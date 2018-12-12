<?php

namespace Ridex\TimedMessages;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Message;

class TimedMessageItem extends Model
{
    protected $fillable = [
        'audio_file', 'audio_id', 'photo_file', 'photo_id', 'document_file', 'document_id', 'buttons', 'text', 'url'
    ];

    public function timedMessage() {
        return $this->belongsTo('App\TimedMessage');
    }

    public function send($user) {
        $timedMessage = $this->timedMessage()->first();

        if ($timedMessage->type == 0) {
            if (!empty($this->buttons)) {
                $buttons = explode("\n", $this->buttons);

                $keyboard_buttons = [];

                foreach ($buttons as $button) {
                    $tmp = explode('|', $button);

                    if (count($tmp) == 1) {
                        $tmp = explode(':', $button);

                        array_push($keyboard_buttons, [
                            'text' => trim($tmp[0]),
                            'callback_data' => trim($tmp[1])
                        ]);
                    } else {
                        array_push($keyboard_buttons, [
                            'text' => trim($tmp[0]),
                            'url' => trim($tmp[1])
                        ]);
                    }
                }

                $keyboard = Telegram::replyKeyboardMarkup([
                    'inline_keyboard' => array_chunk($keyboard_buttons, 1)
                ]);
            } else if (get_class($user) == 'App\User') {
                $keyboard = Telegram::replyKeyboardMarkup([
                    'keyboard' => $user->challengeDayMenu(),
                    'resize_keyboard' => true,
                    'one_time_keyboard' => false
                ]);
            }

            if ($this->text) {
                $text = $this->text;

                $text = str_replace("{{name}}", $user->name, $text);
                $text = str_replace("{{payment_liqpay_url}}", env('APP_URL').'/payment/liqpay/' . $user->id, $text);
                $text = str_replace("{{payment_fondy_url}}", env('APP_URL').'/payment/fondy/' . $user->id, $text);

                if ($this->url) {
                    $text = "<a href=\"" . $this->url . "\">&#8204;</a>" . $text;
                }

                try {
                    Telegram::sendMessage([
                        'chat_id' => $user->tg_chat_id,
                        'text' => $text,
                        'parse_mode' => 'HTML',
                        'reply_markup' => $keyboard
                    ]);
                } catch (\Exception $exception) { }

                if (!empty($this->buttons)) {
                    $keyboard = Telegram::replyKeyboardMarkup([
                        'keyboard' => $user->challengeDayMenu(),
                        'resize_keyboard' => true,
                        'one_time_keyboard' => false
                    ]);
                }

                usleep(150 * 1000);
            }

            // Audio
            if (is_null($this->audio_id) && !empty($this->audio_file)) {
                try {
                    /* @var $message Message */
                    $message = Telegram::sendAudio([
                        'chat_id' => $user->tg_chat_id,
                        'audio' => public_path('uploads/' . $this->audio_file),
                        'reply_markup' => $keyboard
                    ]);

                    if (!empty($this->buttons)) {
                        $keyboard = Telegram::replyKeyboardMarkup([
                            'keyboard' => $user->challengeDayMenu(),
                            'resize_keyboard' => true,
                            'one_time_keyboard' => false
                        ]);
                    }

                    if ($audio = $message->getAudio()) {
                        $this->audio_id = $audio->getFileId();
                        $this->save();
                    }
                } catch (\Exception $exception) { }

                usleep(150 * 1000);
            } else if (!is_null($this->audio_id)) {
                try {
                    Telegram::sendAudio([
                        'chat_id' => $user->tg_chat_id,
                        'audio' => $this->audio_id,
                        'reply_markup' => $keyboard
                    ]);
                } catch (\Exception $exception) { }

                if (!empty($this->buttons)) {
                    $keyboard = Telegram::replyKeyboardMarkup([
                        'keyboard' => $user->challengeDayMenu(),
                        'resize_keyboard' => true,
                        'one_time_keyboard' => false
                    ]);
                }

                usleep(150 * 1000);
            }
            //

            // Photo
            if (is_null($this->photo_id) && !empty($this->photo_file)) {
                try {
                    /* @var $message Message */
                    $message = Telegram::sendPhoto([
                        'chat_id' => $user->tg_chat_id,
                        'photo' => public_path('uploads/' . $this->photo_file),
                        'reply_markup' => $keyboard
                    ]);

                    if (!empty($this->buttons)) {
                        $keyboard = Telegram::replyKeyboardMarkup([
                            'keyboard' => $user->challengeDayMenu(),
                            'resize_keyboard' => true,
                            'one_time_keyboard' => false
                        ]);
                    }

                    if ($photos = $message->getPhoto()) {
                        $photo = $photos->get($photos->count() - 1);

                        $this->photo_id = $photo->getFileId();

                        $this->save();
                    }
                } catch (\Exception $exception) { }

                usleep(150 * 1000);
            } else if (!is_null($this->photo_id)) {
                try {
                    Telegram::sendPhoto([
                        'chat_id' => $user->tg_chat_id,
                        'photo' => $this->photo_id,
                        'reply_markup' => $keyboard
                    ]);
                } catch (\Exception $exception) { }

                if (!empty($this->buttons)) {
                    $keyboard = Telegram::replyKeyboardMarkup([
                        'keyboard' => $user->challengeDayMenu(),
                        'resize_keyboard' => true,
                        'one_time_keyboard' => false
                    ]);
                }

                usleep(150 * 1000);
            }
            //

            // Document
            if (is_null($this->document_id) && !empty($this->document_file)) {
                try {
                    /* @var $message Message */
                    $message = Telegram::sendDocument([
                        'chat_id' => $user->tg_chat_id,
                        'document' => public_path('uploads/' . $this->document_file),
                        'reply_markup' => $keyboard
                    ]);

                    if (!empty($this->buttons)) {
                        $keyboard = Telegram::replyKeyboardMarkup([
                            'keyboard' => $user->challengeDayMenu(),
                            'resize_keyboard' => true,
                            'one_time_keyboard' => false
                        ]);
                    }

                    if ($document = $message->getDocument()) {
                        $this->document_id = $document->getFileId();

                        $this->save();
                    }
                } catch (\Exception $exception) { }

                usleep(150 * 1000);
            } else if (!is_null($this->document_id)) {
                try {
                    Telegram::sendDocument([
                        'chat_id' => $user->tg_chat_id,
                        'document' => $this->document_id,
                        'reply_markup' => $keyboard
                    ]);
                } catch (\Exception $exception) { }

                if (!empty($this->buttons)) {
                    $keyboard = Telegram::replyKeyboardMarkup([
                        'keyboard' => $user->challengeDayMenu(),
                        'resize_keyboard' => true,
                        'one_time_keyboard' => false
                    ]);
                }

                usleep(150 * 1000);
            }
        } else if ($timedMessage->type == 1) {
            $userInfoData = $user->info_data;

            if (isset($userInfoData['email'])) {
                try {
                    Mail::send([], [], function ($message) use ($timedMessage, $userInfoData, $user) {
                        $message->from(config('timed_messages.email.email'), config('timed_messages.email.email_user'));

                        $message->to($userInfoData['email'], $user->name)
                            ->subject($timedMessage->name)
                            ->setBody($this->text, 'text/html');
                    });
                } catch (\Exception $exception) { }
            }
        }
    }
}