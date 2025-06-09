<?php

use d3system\yii2\web\D3SystemView;
use eaArgonTheme\widget\ThAlertList;
use eaArgonTheme\widget\ThReturnButton;



/**
* @var D3SystemView $this
* @var d3yii2\d3paymentsystems\models\D3paymentsystemsFee $model
*/

$this->title = Yii::t('d3paymentsystems', 'Create wallet fee record');
$this->addPageButtons(ThReturnButton::widget([
    'backUrl' => ['index'],
]));

?>
<div class="row">
    <?=  ThAlertList::widget()?>
    <div class="col-md-9">
        <div class="panel  rounded shadow">
            <div class="panel-body rounded-bottom">
            <?= $this->render(
                '_form',
                [
                    'model' => $model,
                ]
            )?>
            </div>
        </div>
    </div>
</div>
