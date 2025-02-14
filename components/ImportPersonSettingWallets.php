<?php

namespace d3yii2\d3paymentsystems\components;

use d3yii2\d3paymentsystems\models\D3pPersonContactCrypto;
use d3yii2\d3paymentsystems\models\D3pPersonContactExtInterface;
use Exception;
use yii\helpers\VarDumper;
use yii2d3\d3persons\components\ImportPersonDataCSV;
use yii2d3\d3persons\components\PersonContactTypeInterface;


/**
 *
 * @property-write mixed $fullType
 */
class ImportPersonSettingWallets extends ImportPersonDataCSV
{
    public int $contactTypeId;

    public ?PersonContactTypeInterface $walletComponent = null;

    public array $modelValueMapping = [];

    public array $modelDefaultValues = [];

    /**
     * @param array $row
     * @return bool
     * @throws Exception
     */
    public function proccessRow(array $row): bool
    {
        echo $this->lineNumber . ' ';

        $existingPerson = $this->getExistingPerson($row);

        if (!$existingPerson) {
            return false;
        }

        echo $existingPerson->user->username . ' ';

        $personWallets = [];
        foreach ($existingPerson->d3pPersonContacts as $walletRecord) {
            if ($walletRecord->contact_type === $this->contactTypeId) {
                $walletModel = $walletRecord->findExtendedContactModel();
                $personWallets[$walletModel->id] = $walletModel;
            }
        }
        /**
         * savaac atributus
         */
        $attributes = [];
        foreach ($this->modelValueMapping as $attribute => $position) {
            if (!$position) {
                continue;
            }
            $modelParsedValue = $this->getParsedValue($row, $position);

            if (!$modelParsedValue) {
                continue;
            }

            // Phone validation error corection
            if ($attribute === 'phone' && !empty($modelParsedValue) && $modelParsedValue[0] !== '+') {
                $modelParsedValue = '+' . $modelParsedValue;
            }

            if (!empty($modelParsedValue)) {
                $attributes[$attribute] = $modelParsedValue;
            }
        }
        if (empty($attributes)) {
            echo 'Skipped - empty attributes' . PHP_EOL;
            return false;
        }
        foreach ($this->modelDefaultValues as $defaultAttr => $value) {
            if (!isset($attributes[$defaultAttr])) {
                $attributes[$defaultAttr] = $value;
            }
        }

        $walletModel = $this->walletComponent->createNewModel($existingPerson->id);
        // Missing type correction for Crypto wallets
        if ($walletModel instanceof D3pPersonContactCrypto) {
            $this->setFullType($walletModel);
        }
        // Error corection
        if (isset($attributes['fee'])) {
            $attributes['fee'] = (float)$attributes['fee'];
        }
        if (isset($attributes['recipientFee'])) {
            $attributes['recipientFee'] = (float)$attributes['recipientFee'];
        }
        $walletModel->setAttributes($attributes);
        $walletModel->createContactValue();
        if ($existingWalletModel = $this->getExistingWallet($personWallets, $walletModel->contact_value)) {
            echo ' skip - already exist wallet' . PHP_EOL;
        }
        if (!$walletModel->save()) {
            echo '[ERROR]' . VarDumper::dumpAsString($walletModel->errors) . PHP_EOL;
            $this->failedCounter++;
            return false;
        }
        echo 'OK' . PHP_EOL;
        return true;
}

    /**
     * @param $walletModel
     */
    protected function setFullType(&$walletModel): void
    {
        // Crypto full value defaults
        $typeList = $walletModel->typeList();
        $firstType = array_key_first($typeList);
        $walletModel->fullType = $firstType;
        // Copied from D3pPersonContactCrypto model load()
        $list = explode(':', $firstType);
        if (count($list) === 2) {
            [$walletModel->type, $walletModel->subType] = $list;
        }
    }

    /**
     * @param $walletModel
     * @return string
     */
    protected function getFullType($walletModel): string
    {
        return $walletModel->type . ':' . $walletModel->subType;
    }

    /**
     * @param array $personWallets
     * @param string $parsedValue
     * @return D3pPersonContactExtInterface|null
     */
    protected function getExistingWallet(array $personWallets, string $parsedValue): ?D3pPersonContactExtInterface
    {
        foreach ($personWallets as $model) {
            if ($model->contact_value === $parsedValue) {
                return $model;
            }
        }
        return null;
    }
}
