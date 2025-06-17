<?php

namespace d3yii2\d3paymentsystems\models;

use d3system\dictionaries\SysModelsDictionary;
use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3paymentsystems\models\base\D3paymentsystemsFee as BaseD3paymentsystemsFee;
use http\Exception\RuntimeException;
use Yii;
use yii\base\Exception;

/**
 * This is the model class for table "d3paymentsystems_fee".
 */
class D3paymentsystemsFeeImport extends BaseD3paymentsystemsFee
{
    private int $lineNumber;


    private array $walletsTypeNames = [];
    private array $countries = [];
    private array $walletTypes = [];

    /**
     * @throws D3ActiveRecordException
     */
    public function import(string $file): void
    {
        $this->walletsTypeNames = [
            'skrill multi' => SysModelsDictionary::getIdByClassName(D3pPersonContactSkrill::class),
            'crypto' => SysModelsDictionary::getIdByClassName(D3pPersonContactCrypto::class),
            'Luxon' => SysModelsDictionary::getIdByClassName(D3pPersonContactLuxon::class),
        ];
        foreach (self::optsFromCountry() as $country => $name) {
            $this->countries[strtolower($country)] = $country;
        }

        foreach (self::optsFromType() as $wType => $wName) {
            $this->walletTypes[strtolower($wType)] = $wType;
        }
        $filePath = Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . $file;
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new RuntimeException('Cannot read file: ' . $file);
        }
        $this->lineNumber = 0;
        while (($rawStr = fgets($handle)) !== false) {
            $this->lineNumber++;

            $rowData = str_getcsv(trim($rawStr));
            try {
                $this->processRow($rowData);
            } catch (Exception $e) {
                echo $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
            }
        }

        fclose($handle);
    }

    /**
     * @throws \yii\db\Exception
     * @throws D3ActiveRecordException
     */
    private function processRow(array $rowData): void
    {

        if (!$rowData) {
            return;
        }
        if ($rowData[0] === 'wallet type') {
            return;
        }
        $model = new self();
        [$walletName, $fromCountry, $fromType, $toCountry, $model->sender_fee,, $model->receiver_fee] = $rowData;
        $model->sender_fee = str_replace(',', '.', $model->sender_fee);
        $model->receiver_fee = str_replace(',', '.', $model->receiver_fee);
        $model->wallet_sys_model_id = $this->walletsTypeNames[$walletName];
        $model->from_country = $this->countries[strtolower($fromCountry)];
        $model->from_type = $this->walletTypes[strtolower($fromType)];
        $model->to_country = $this->countries[strtolower($toCountry)];
        if (!$model->save()) {
            throw new D3ActiveRecordException($model);
        }
    }


}

