<?php

namespace d3yii2\d3paymentsystems\models;

interface D3pPersonContactExtInterface extends \yii2d3\d3persons\models\D3pPersonContactExtInterface
{
    public function setStatusActual(): void;

    public function setStatusInactive(): void;

    public function setStatusClosed(): void;

    public function isStatusActual(): bool;

    public function isStatusInactive(): bool;

    public function isStatusClosed(): bool;

    public function isCurrencyMulti(): bool;
}