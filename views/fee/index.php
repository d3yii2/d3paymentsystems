<?php

use d3system\yii2\web\D3SystemView;
use eaArgonTheme\widget\ThAlertList;
use eaArgonTheme\widget\ThButton;
use eaArgonTheme\widget\ThDataListColumn;
use eaArgonTheme\widget\ThGridView;
use yii\widgets\Pjax;


/**
 * @var D3SystemView $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var d3yii2\d3paymentsystems\models\D3paymentsystemsFeeSearch $searchModel
 */

$this->title = Yii::t('d3paymentsystems', 'Wallets fee');
$this->addPageButtons(ThButton::widget([
    'tooltip' => Yii::t('crud', 'Create new record'),
    'link' => ['create'],
    'icon' => ThButton::ICON_PLUS,
    'type' => ThButton::TYPE_SUCCESS
]));
$this->setPageWiki('d3paymentsystems-fee-index');
$actionColumnTemplate = '{update}{delete}';
?>
<div class="row">
    <?= ThAlertList::widget() ?>
    <div class="col-md-12">
        <?php
        Pjax::begin(['id' => 'pjax-main', 'enablePushState' => false,]);
        echo ThGridView::widget([
            'id' => 'pjax-main',
            'dataProvider' => $dataProvider,
            'actionColumnTemplate' => $actionColumnTemplate,
            //'filterModel' => $searchModel,
            'columns' => [
                [
                    'attribute' => 'wallet_sys_model_id',
                    'class' => ThDataListColumn::class,
                    'list' => $searchModel::optsWalletSysModelId(),
                ],

                'sender_fee',
                'receiver_fee',
                [
                    'attribute' => 'from_country',
                    'class' => ThDataListColumn::class,
                    'list' => $searchModel::optsFromCountry(),
                ],
                [
                    'attribute' => 'from_type',
                    'class' => ThDataListColumn::class,
                    'list' => $searchModel::optsFromType(),
                ],
                [
                    'attribute' => 'to_country',
                    'class' => ThDataListColumn::class,
                    'list' => $searchModel::optsToCountry(),
                ],
                [
                    'attribute' => 'to_type',
                    'class' => ThDataListColumn::class,
                    'list' => $searchModel::optsToType(),
                ],
            ],
        ]);
        Pjax::end();
        ?>
    </div>
</div>
