<?php

namespace d3yii2\d3paymentsystems\commands;

use d3yii2\d3paymentsystems\components\PersonSettingCrypto;
use d3yii2\d3paymentsystems\components\PersonSettingLuxon;
use d3yii2\d3paymentsystems\components\PersonSettingSkrill;
use d3yii2\d3paymentsystems\dictionaries\CurrenciesDictionary;
use Exception;
use Yii;
use yii\console\Controller;
use d3yii2\d3paymentsystems\components\ImportPersonSettingWallets;
use yii2d3\d3persons\components\PersonContactTypeInterface;

class ImportPersonWallet extends Controller
{
    /**
     * @param string $fileName
     * @param int $sysCompayId
     * @param int $nameColumn
     * @param int $currencyColumn
     * @param int $feeColumn
     * @param int $recipientFeeColumn
     */
    public function actionSkrill(
        string $fileName,
        int $nameColumn,
        int $currencyColumn,
        int $feeColumn,
        int $recipientFeeColumn,
        int $sysCompayId = 1
    ):void
    {
        /** @var PersonContactTypeInterface $component */
        $component = Yii::$app->{PersonSettingSkrill::NAME};
        
        $importer = new ImportPersonSettingWallets([
            'fileName' => $fileName,
            'sysCompayId' => $sysCompayId,
            'contactTypeId' => $component->contactTypeId,
            'walletComponent' => $component,
            'modelValueMapping' => [
                'contact_value' => $nameColumn,
                'currency' => $currencyColumn,
                'fee' => $feeColumn,
                'recipientFee' => $recipientFeeColumn,
            ]
        ]);
        
        $this->import($importer);
    }

    /**
     * @param string $fileName
     * @param int $sysCompayId
     */
    public function actionLuxon(
        string $fileName,
        int $fullNameColumn,
        int $emailColumn,
        int $phoneColumn,
        int $sysCompayId = 1):void
    {
        /** @var PersonContactTypeInterface $component */
        $component = Yii::$app->{PersonSettingLuxon::NAME};
        
        $importer = new ImportPersonSettingWallets([
            'fileName' => $fileName,
            'sysCompayId' => $sysCompayId,
            'contactTypeId' => $component->contactTypeId,
            'walletComponent' => $component,
            //'mappingColumnIndex' => 2,
            'modelValueMapping' => [
                'fullName' => $fullNameColumn,
                'email' => $emailColumn,
                'phone' => $phoneColumn,
            ],
            'modelDefaultValues' => [
                'currency' => CurrenciesDictionary::CURRENCY_MULTI,
            ]
        ]);
        
        $this->import($importer);
    }

    /**
     * @param string $fileName
     * @param int $sysCompayId
     */
    public function actionCrypto(string $fileName, $cryptoColumn, int $sysCompayId = 1):void
    {
        /** @var PersonContactTypeInterface $component */
        $component = Yii::$app->{PersonSettingCrypto::NAME};

        $importer = new ImportPersonSettingWallets([
            'fileName' => $fileName,
            'sysCompayId' => $sysCompayId,
            'contactTypeId' => $component->contactTypeId,
            'walletComponent' => $component,
            //'mappingColumnIndex' => 3,
            'modelValueMapping' => [
                'contact_value' => $cryptoColumn,
            ],
        ]);
        
        $this->import($importer);
    }
    
    protected function import($importer):void
    {
        try {
            $importer->run();
        } catch (Exception $e) {
            echo $e->getMessage();
            Yii::error($e->getMessage() . PHP_EOL . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

    }
}