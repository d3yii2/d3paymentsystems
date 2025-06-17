<?php

namespace d3yii2\d3paymentsystems\commands;

use d3yii2\d3paymentsystems\components\PersonSettingCrypto;
use d3yii2\d3paymentsystems\components\PersonSettingLuxon;
use d3yii2\d3paymentsystems\components\PersonSettingSkrill;
use d3yii2\d3paymentsystems\dictionaries\CurrenciesDictionary;
use d3yii2\d3paymentsystems\models\D3paymentsystemsFeeImport;
use Exception;
use Yii;
use yii\console\Controller;
use d3yii2\d3paymentsystems\components\ImportPersonSettingWallets;
use yii2d3\d3persons\components\PersonContactTypeInterface;

class ImportPersonWallet extends Controller
{
    /**
     * @param string $fileName
     * @param int $nameColumn
     * @param int $currencyColumn
     * @param int $feeColumn
     * @param int $recipientFeeColumn
     */
    public function actionSkrill(
        string $fileName,
        int $nameColumn,
        int $currencyColumn,
        int $countryColumn,
        int $typeColumn
    ):void
    {
        /** @var PersonContactTypeInterface $component */
        $component = Yii::$app->{PersonSettingSkrill::NAME};
        
        $importer = new ImportPersonSettingWallets([
            'fileName' => $fileName,
            'contactTypeId' => $component->contactTypeId,
            'walletComponent' => $component,
            'modelValueMapping' => [
                'contact_value' => $nameColumn,
                'currency' => $currencyColumn,
                'country' => $countryColumn,
                'type' => $typeColumn,
            ],
            'isEqualAttributes' => ['contact_value']
        ]);
        
        $this->import($importer);
    }

    /**
     * @param string $fileName
     * @param int $emailColumn
     * @param int $countryColumn
     * @param int $typeColumn
     */
    public function actionLuxon(
        string $fileName,
        int $emailColumn,
        int $countryColumn,
        int $typeColumn
    ):void
    {
        /** @var PersonContactTypeInterface $component */
        $component = Yii::$app->{PersonSettingLuxon::NAME};
        
        $importer = new ImportPersonSettingWallets([
            'fileName' => $fileName,
            'contactTypeId' => $component->contactTypeId,
            'walletComponent' => $component,
            //'mappingColumnIndex' => 2,
            'modelValueMapping' => [
                'email' => $emailColumn,
                'country' => $countryColumn,
                'type' => $typeColumn,
            ],
            'modelDefaultValues' => [
                'currency' => CurrenciesDictionary::CURRENCY_MULTI,
            ],
            'isEqualAttributes' => ['email']
        ]);
        
        $this->import($importer);
    }

    /**
     * @param string $fileName
     * @param int $cryptoColumn
     * @param int $countryColumn
     * @param int $typeColumn
     */
    public function actionCrypto(
        string $fileName,
        int $cryptoColumn,
        int $countryColumn,
        int $typeColumn
    ):void
    {
        /** @var PersonContactTypeInterface $component */
        $component = Yii::$app->{PersonSettingCrypto::NAME};

        $importer = new ImportPersonSettingWallets([
            'fileName' => $fileName,
            'contactTypeId' => $component->contactTypeId,
            'walletComponent' => $component,
            'modelValueMapping' => [
                'contact_value' => $cryptoColumn,
                'country' => $countryColumn,
                'type' => $typeColumn,
            ],
            'isEqualAttributes' => ['contact_value']
        ]);
        
        $this->import($importer);
    }

    public function actionImportFee(string $fileName):void
    {
        $model = new D3paymentsystemsFeeImport();
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            $model->import($fileName);
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
            echo $e->getMessage() . PHP_EOL . $e->getTraceAsString();
        }
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