<?php

namespace d3yii2\d3paymentsystems\commands;

use d3yii2\d3paymentsystems\components\PersonSettingCrypto;
use d3yii2\d3paymentsystems\components\PersonSettingLuxon;
use d3yii2\d3paymentsystems\components\PersonSettingSkrill;
use d3yii2\d3paymentsystems\dictionaries\CurrenciesDictionary;
use d3yii2\d3paymentsystems\models\D3pPersonContactCrypto;
use Yii;
use yii\console\Controller;
use d3yii2\d3paymentsystems\components\ImportPersonSettingWallets;
use yii2d3\d3persons\components\PersonContactTypeInterface;
use yii2d3\d3persons\dictionaries\D3pContactTypeDictionary;
use yii2d3\d3persons\models\D3pContactType;

class ImportPersonWallet extends Controller
{
    /**
     * @param string $fileName
     * @param int $sysCompayId
     */
    public function actionSkrill(string $fileName, int $sysCompayId = 1)
    {
        /** @var PersonContactTypeInterface $component */
        $component = Yii::$app->{PersonSettingSkrill::NAME};
        
        $importer = new ImportPersonSettingWallets([
            'fileName' => $fileName,
            'sysCompayId' => $sysCompayId,
            'contactTypeId' => $component->contactTypeId,
            'walletComponent' => $component,
            'modelValueMapping' => [
                'contact_value' => 3,
                'currency' => 4,
                'fee' => 5,
            ]
        ]);
        
        $this->import($importer);
    }

    /**
     * @param string $fileName
     * @param int $sysCompayId
     */
    public function actionLuxon(string $fileName, int $sysCompayId = 1)
    {
        /** @var PersonContactTypeInterface $component */
        $component = Yii::$app->{PersonSettingLuxon::NAME};
        
        $importer = new ImportPersonSettingWallets([
            'fileName' => $fileName,
            'sysCompayId' => $sysCompayId,
            'contactTypeId' => $component->contactTypeId,
            'walletComponent' => $component,
            'mappingColumnIndex' => 2,
            'modelValueMapping' => [
                'fullName' => 7,
                'email' => 8,
                'phone' => 9,
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
    public function actionCrypto(string $fileName, int $sysCompayId = 1)
    {
        /** @var PersonContactTypeInterface $component */
        $component = Yii::$app->{PersonSettingCrypto::NAME};

        $importer = new ImportPersonSettingWallets([
            'fileName' => $fileName,
            'sysCompayId' => $sysCompayId,
            'contactTypeId' => $component->contactTypeId,
            'walletComponent' => $component,
            'mappingColumnIndex' => 2,
            'modelValueMapping' => [
                'contact_value' => 6,
            ],
        ]);
        
        $this->import($importer);
    }
    
    protected function import($importer)
    {
        try {
            $importer->run();
        } catch (\Exception $e) {
            echo $e->getMessage();
            Yii::error($e->getMessage() . PHP_EOL . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

    }
}