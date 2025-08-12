<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<div class="patches-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <?= $form->field($model, 'id_patches') ?>
    <?= $form->field($model, 'timestamp') ?>
    <?= $form->field($model, 'description') ?>
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-warning']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn btn-outline-light']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
