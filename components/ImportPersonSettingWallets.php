<?php

namespace d3yii2\d3paymentsystems\components;

use d3yii2\d3paymentsystems\components\PersonSettingCrypto;
use d3yii2\d3paymentsystems\components\PersonSettingLuxon;
use d3yii2\d3paymentsystems\components\PersonSettingSkrill;
use d3yii2\d3paymentsystems\models\D3pPersonContactCrypto;
use d3yii2\d3paymentsystems\models\D3pPersonContactExtInterface;
use yii\base\Component;
use Yii;
use yii\helpers\ArrayHelper;
use yii2d3\d3persons\components\ImportPersonDataCSV;
use yii2d3\d3persons\components\PersonContactTypeInterface;
use yii2d3\d3persons\dictionaries\D3pContactTypeDictionary;
use yii2d3\d3persons\models\D3pContactType;
use yii2d3\d3persons\models\D3pPerson;
use yii2d3\d3persons\models\D3pPersonContact;
use yii2d3\d3persons\models\User;

class ImportPersonSettingWallets extends ImportPersonDataCSV
{
    public int $sysCompayId;

    public int $contactTypeId;

    public ?PersonContactTypeInterface $walletComponent = null;

    public array $modelValueMapping = [];

    public array $modelDefaultValues = [];

    /**
     * @param array $row
     * @return bool|D3pPersonContact
     * @throws \Exception
     */
    public function proccessRow(array $row)
    {
        echo $this->lineNumber . ' ';

        $existingPerson = $this->getExistingPerson($row);

        if ($existingPerson) {

            echo $existingPerson->user->username . ' ';

            $personWallets = [];
            foreach ($existingPerson->d3pPersonContacts as $walletRecord) {
                if ($walletRecord->contact_type === $this->contactTypeId) {
                    $walletModel = $walletRecord->findExtendedContactModel();
                    $personWallets[$walletModel->id] = $walletModel;
                }
            }

            $saveModel = false;

            $attributes = [];

            foreach ($this->modelValueMapping as $attribute => $position) {
                $modelParsedValue = $this->getParsedValue($row, $position);
                
                if (!$modelParsedValue) {
                    continue;
                }

                // Phone validation error corection
                if ($attribute === 'phone' && !empty($modelParsedValue) && substr($modelParsedValue, 0, 1) !== '+') {
                    $modelParsedValue = '+' . $modelParsedValue;
                }

                if (!empty($modelParsedValue)) {
                    $attributes[$attribute] = $modelParsedValue;
                }
            }

            if (!empty($attributes)) {
                foreach ($this->modelDefaultValues as $defaultAttr => $value) {
                    if (!isset($attributes[$defaultAttr])) {
                        $attributes[$defaultAttr] = $value;
                    }
                }
            }
            
            if (!empty($attributes)) {

                $parsedContactValue = $attributes['contact_value'] ?? null;

                $walletModel = $parsedContactValue ? $this->getExistingWallet($personWallets, $parsedContactValue) : null;
                
                if (!$walletModel) {

                    $walletModel = $this->walletComponent->createNewModel($existingPerson->id);
                    $walletModel->setAttributes($attributes);

                    // Missing type correction for Crypto wallets
                    if ($walletModel instanceof D3pPersonContactCrypto) {
                        $this->setFullType($walletModel);
                    }
                } else {
                   
                    // Missing type correction for Crypto wallets
                    if ($walletModel instanceof D3pPersonContactCrypto && empty($walletModel->fullType)) {
                        $walletModel->fullType = $this->getFullType($walletModel);
                    }
                }

                $walletModel->setAttributes($attributes);

                if (!$walletModel->save()) {
                    echo '[ERROR]' . json_encode($walletModel->errors) . PHP_EOL;
                    $this->failedCounter++;
                    return false;
                }

                echo 'OK' . PHP_EOL;
                return true;
            }
        }

        echo 'Skipped' . PHP_EOL;
        return false;
    }

    /**
     * @param $walletModel
     */
    protected function setFullType(&$walletModel)
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
