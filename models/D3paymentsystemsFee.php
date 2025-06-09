<?php

namespace d3yii2\d3paymentsystems\models;

use d3system\dictionaries\SysModelsDictionary;
use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3paymentsystems\models\base\D3paymentsystemsFee as BaseD3paymentsystemsFee;
use yii\web\HttpException;

/**
 * This is the model class for table "d3paymentsystems_fee".
 */
class D3paymentsystemsFee extends BaseD3paymentsystemsFee
{
    /**
     * @throws HttpException
     */
    public static function findForController(int $id): self
    {
        if (($model = self::findOne($id)) === null) {
            throw new HttpException(404, 'The requested page does not exist.');
        }
        return $model;
    }

    /**
     * @throws D3ActiveRecordException
     */
    public static function optsWalletSysModelId(): array
    {
        return [
            SysModelsDictionary::getIdByClassName(D3pPersonContactCrypto::class) => 'Crypto',
            SysModelsDictionary::getIdByClassName(D3pPersonContactLuxon::class) => 'Luxon',
            SysModelsDictionary::getIdByClassName(D3pPersonContactSkrill::class) => 'Skrill',
        ];
    }
}

