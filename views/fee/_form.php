<?php

use kartik\form\ActiveForm;
use eaArgonTheme\widget\ThButton;

/**
 * @var yii\web\View $this
 * @var d3yii2\d3paymentsystems\models\D3paymentsystemsFee $model
 * @var yii\widgets\ActiveForm $form
 */
?>
<div class="form-body">
    <?php
    $form = ActiveForm::begin([
        'id' => 'D3paymentsystemsFee',
//        'layout' => 'default',
        'enableClientValidation' => true,
        'errorSummaryCssClass' => 'error-summary alert alert-error',
    ]);
    ?>
    <?= $form->errorSummary($model) ?>
    <?= $form
        ->field($model, 'wallet_sys_model_id')
        ->dropDownList(d3yii2\d3paymentsystems\models\D3paymentsystemsFee::optsWalletSysModelId()) ?>

    <?= $form
        ->field($model, 'from_country')
        ->dropDownList(d3yii2\d3paymentsystems\models\D3paymentsystemsFee::optsFromCountry()) ?>
    <?= $form
        ->field($model, 'from_type')
        ->dropDownList(d3yii2\d3paymentsystems\models\D3paymentsystemsFee::optsFromType()) ?>
    <?= $form
        ->field($model, 'to_country')
        ->dropDownList(d3yii2\d3paymentsystems\models\D3paymentsystemsFee::optsToCountry()) ?>
    <?= $form->field($model, 'sender_fee')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'receiver_fee')->textInput(['maxlength' => true]) ?>
    <div class="form-footer">
        <div class="pull-right">
            <?= ThButton::widget([
                'label' => ($model->isNewRecord ? Yii::t('crud', 'Create') : Yii::t('crud', 'Save')),
                'id' => 'save-' . $model->formName(),
                'icon' => ThButton::ICON_CHECK,
                'type' => ThButton::TYPE_SUCCESS,
                'submit' => true,
                'htmlOptions' => [
                    'name' => 'action',
                    'value' => 'save',
                ],
            ]) ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
