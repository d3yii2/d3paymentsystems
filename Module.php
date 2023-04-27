<?php

namespace d3yii2\d3paymentsystems;

use Yii;
use d3system\yii2\base\D3Module;

class Module extends D3Module
{
    public $controllerNamespace = 'd3yii2\d3paymentsystems\controllers';

    public $leftMenu = 'd3yii2\d3paymentsystems\LeftMenu';

    public function getLabel(): string
    {
        return Yii::t('d3paymentsystems','D3 Payment Systems');
    }
}
