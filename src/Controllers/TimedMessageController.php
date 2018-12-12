<?php

namespace Ridex\TimedMessages\Controllers;

use Ridex\TimedMessages\TimedMessage;
use Ridex\TimedMessages\TimedMessageRule;
use Behat\Transliterator\Transliterator;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\UploadedFile;

use Illuminate\Routing\Controller as BaseController;

class TimedMessageController extends BaseController
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Отложенные рассылки')
            ->description('')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Отложенная рассылка #'.$id)
            ->description('')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Отложенная рассылка #'.$id)
            ->description('')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Новая отложенная рассылка')
            ->description('')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TimedMessage);

        $grid->id('ID');

        $grid->name('Название');

        $grid->user_type('Тип пользователя')->display(function ($value) {
            return TimedMessage\UserType::toArray()[$value];
        });

        $grid->column('column', 'Метка')->display(function ($value) {
            return TimedMessage::COLUMNS[$value];
        });
        $grid->minutes('Минуты');

        $grid->column('time', 'Действие')->display(function ($value) {
            $result = [];

            if ($this->before_start_date) {
                $result[] = '<span class="label label-success">До начала марафона</span>';
            }

            if ($this->after_start_date) {
                $result[] = '<span class="label label-success">После начала марафона</span>';
            }

            return implode('&nbsp;', $result);
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(TimedMessage::findOrFail($id));

        $show->id('ID');

        $show->name('Название');

        $show->user_type('Тип пользователя')->as(function ($value) {
            return TimedMessage\UserType::toArray()[$value];
        });

        $show->minutes('Минуты');

        $show->created_at('Дата создания');
        $show->updated_at('Дата изменения');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TimedMessage);

        $form->text('name', 'Название');

        $form->divider();

        $form->select('type', 'Тип рассылки')->options(TimedMessage\TimedMessageType::toArray())->allowClear(false);

        $form->divider();

        $form->switch('before_start_date', 'До начала марафона');
        $form->switch('after_start_date', 'После начала марафона');

        $form->divider();

        $form->select('user_type', 'Тип пользователя')
            ->options(TimedMessage\UserType::toArray())
            ->allowClear(false);

        $form->divider();

        $form->select('column', 'Метка')
            ->options(TimedMessage::COLUMNS)
            ->allowClear(false);
        $form->number('minutes', 'Минуты');

        $form->hasMany('rules', 'Правила', function (Form\NestedForm $nestedForm) {
            $nestedForm->select('column', 'Поле')
                ->options(config('timed_messages.rules.columns'))
                ->allowClear(false);

            $nestedForm->select('operator', 'Оператор')
                ->options(TimedMessageRule::OPERATORS)
                ->allowClear(false);

            $nestedForm->text('value', 'Значение');
        });

        $form->hasMany('items', 'Сообщения', function (Form\NestedForm $nestedForm) use ($form) {
            $nestedForm->textarea('text', 'Текст')->rows(10);

            $nestedForm->file('photo_file', 'Фотография')
                ->name(function (UploadedFile $file) use ($form) {
                    return Transliterator::transliterate(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '_').'.'.$file->getClientOriginalExtension();
                })
                ->move('timed_messages');

            $nestedForm->file('audio_file', 'Аудио')
                ->name(function (UploadedFile $file) use ($form) {
                    return Transliterator::transliterate(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '_').'.'.$file->getClientOriginalExtension();
                })
                ->move('timed_messages');

            $nestedForm->file('document_file', 'Файл')
                ->name(function (UploadedFile $file) use ($form) {
                    return Transliterator::transliterate(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '_').'.'.$file->getClientOriginalExtension();
                })
                ->move('timed_messages');

            $nestedForm->text('url', 'URL');

            $nestedForm->textarea('buttons', 'Кнопки')->rows(2)->help('В формате "text|url", каждая кнопка на новой строке');
        });

        $form->saved(function (Form $form) {
            foreach ($form->model()->items as &$item) {
                if ($item->text == '') {
                    $item->text = null;
                }

                if ($item->audio_file != '') {
                    $item->audio_id = null;
                }

                if ($item->photo_file != '') {
                    $item->photo_id = null;
                }

                if ($item->document_file != '') {
                    $item->document_id = null;
                }

                $item->save();
            }
        });

        return $form;
    }
}