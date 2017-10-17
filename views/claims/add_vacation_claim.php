<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Подать заявление на отпуск - Volvo Trucks';
?>

<div class="container">
    <div class="row">
        <?php $form = ActiveForm::begin(); ?>
        <div class="col l12 s12">
            <div class="card grey lighten-4">
                <div class="card-content">
                    <span class="card-title">Укажите дату, до которой отпуск (мас. 3 месяца)</span>
                    <div class="input-field">
                        <i class="material-icons prefix">event</i>
                        <?= $form->field($model, 'to_date')->input('date', ['class' => 'datepicker-add-claim'])->error(false) ?>
                        <script>
                            var date = new Date();
                            $datepicker = $('.datepicker-add-claim').pickadate({
                                min: true,
                                max: new Date(date.getFullYear(), date.getMonth() + 3, date.getDate()),
                                today: 'Сегодня',
                                clear: 'Очистить',
                                close: 'Закрыть',
                                monthsFull: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                                monthsShort: ['Янв', 'Фев', 'Март', 'Апр', 'Май', 'Июнь', 'Июль', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
                                weekdaysFull: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
                                weekdaysShort: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                                selectMonths: true, // Creates a dropdown to control month
                                selectYears: 60, // Creates a dropdown of 15 years to control year
                                firstDay: 'Понедельник',
                                formatSubmit: 'yyyy-mm-dd',
                                hiddenName: true
                            });
                        </script>
                    </div>
                </div>
                <div class="card-action">
                    <?=Html::submitButton('Подать заявление') ?>
                </div>
            </div>
            <div class="card-action"></div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php if($model->hasErrors()) : ?>
    <script>
        <?php foreach ($model->errors as $error): ?>
        Materialize.toast('<?= $error[0] ?>', 6000);
        <?php endforeach; ?>
    </script>
<?php endif ?>