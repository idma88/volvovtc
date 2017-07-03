<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$action = $_GET['action'] == 'edit' ? 'Редактировать' : 'Добавить';
$this->title = $action . ' трейлер - Volvo Trucks';

?>

<div class="container row">
    <?php $form = ActiveForm::begin([
        'fieldConfig' => [
            'template' => "<div class=\"input-field col s12\">{label}{input}</div>",
            'options' => ['class' => 'row'],
        ]]) ?>
    <h5 class="light col s12"><?= $action ?> трейлер</h5>
    <div class="col l6 m6 s12">
        <div class="card-panel grey lighten-4">
            <?php if($model->picture == null) : ?>
                <img src="<?= Yii::$app->request->baseUrl ?>/images/trailers/default.jpg" class="responsive-img z-depth-3">
            <?php else: ?>
                <img src="<?= Yii::$app->request->baseUrl ?>/images/trailers/<?= $model->picture ?>" class="materialboxed responsive-img z-depth-3">
            <?php endif ?>
            <div class="file-field">
                <div class="btn indigo darken-3 waves-effect waves-light">
                    <span>Новое изображение</span>
                    <?= $form->field($model, 'picture', ['template' => '{input}'])->fileInput(['tag' => false])->label(false) ?>
                </div>
                <div class="file-path-wrapper input-field">
                    <input class="file-path validate" type="text">
                </div>
            </div>
        </div>
    </div>
    <div class="col l6 m6 s12">
        <div class="card-panel grey lighten-4">
            <?= $form->field($model, 'name')->textInput() ?>
            <?= $form->field($model, 'description')->textarea(['class' => 'materialize-textarea']) ?>
            <?= $form->field($model, 'game', ['template' => '{input}{label}'])
                ->radioList([
                    'ets' => 'Euro Truck Simulator 2',
                    'ats' => 'American Truck Simulator'
                ], [
                    'item' => function($index, $label, $name, $checked, $value) {
                        $return = '<p><input type="radio" name="' . $name . '" value="' . $value.'" id="'.$value.'"';
                        if($checked) $return .= ' checked';
                        $return .= '><label for="'.$value.'">'.$label.'</label></p>';
                        return $return;
                    }
                ])->label(false) ?>
        </div>
    </div>
    <div class="fixed-action-btn vertical">
        <?=Html::submitButton(Html::tag('i', 'save', [
            'class' => 'large material-icons'
        ]), ['class' => 'btn-floating btn-large red']) ?>
        <ul>
            <li>
                <a onclick='return confirm("Удалить?")' href="<?=Url::to([
                    'site/trailers',
                    'id' => $model->id,
                    'action' => 'delete'
                ])?>" class="btn-floating yellow darken-3 tooltipped" data-position="left" data-tooltip="Удалить">
                    <i class="material-icons">delete</i>
                </a>
            </li>
        </ul>
    </div>
    <?php ActiveForm::end() ?>
</div>