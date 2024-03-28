<?php

namespace d3yii2\d3paymentsystems\components;

use d3yii2\d3paymentsystems\components\PersonSettingCrypto;
use d3yii2\d3paymentsystems\components\PersonSettingLuxon;
use d3yii2\d3paymentsystems\components\PersonSettingSkrill;
use d3yii2\d3paymentsystems\models\D3pPersonContactCrypto;
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

    protected array $contactTypes = [];

    public function init(): void
    {
        parent::init();

        $this->contactTypes = D3pContactTypeDictionary::getClassList();
    }

    /**
     * @param array $row
     * @return bool|D3pPersonContact
     * @throws \Exception
     */
    public function proccessRow(array $row)
    {
        $existingPerson = $this->getExistingPerson($row);

        if ($existingPerson) {

            $existingWallets = [];

            foreach ($existingPerson->d3pPersonContacts as $personContact) {
                $contactTypeClass = $this->contactTypes[$personContact->contact_type] ?? null;
                $existingWallets[$personContact->contact_type] = $personContact;
            }

            $saveModel = false;
            
            if (!isset($existingWallets[$this->contactTypeId])) {
                $attributes = [];
                
                foreach ($this->modelValueMapping as $attribute => $position) {
                    $modelParsedValue = $this->getParsedValue($row, $position);
                    
                    // Error corection
                    if (in_array($attribute, ['fee'])) {
                        $modelParsedValue = (int)$modelParsedValue;
                    }
                    
                    // Phone validation error corection
                    if ($attribute === 'phone' && !empty($modelParsedValue)  && substr($modelParsedValue, 0, 1) !== '+') {
                        $modelParsedValue = '+' . $modelParsedValue;
                    }
                    
                    if (!empty($modelParsedValue)) {
                        $attributes[$attribute] = $modelParsedValue;
                    }
                }

                if (!empty($attributes)) {

                    foreach ($this->modelDefaultValues as $attribute => $value) {
                        if (!isset($attributes[$attribute])) {
                            $attributes[$attribute] = $value;
                        }
                    }

                    $walletModel = $this->walletComponent->createNewModel($existingPerson->id);
                    $walletModel->setAttributes($attributes);

                    if ($walletModel instanceof D3pPersonContactCrypto) {
                        $this->setFullType($walletModel);
                    }
                    
                    $saveModel = true;
                }
            } else {
                $existingWallet = $existingWallets[$this->contactTypeId];
                $model = D3pPersonContact::findForController($existingWallet->id);
                $walletModel = $model->findExtendedContactModel();
               
                $attributes = [];
                
                foreach ($this->modelValueMapping as $attribute => $position) {
                    $modelParsedValue = $this->getParsedValue($row, $position);
                    if (in_array($attribute, ['fee'])) {
                        $modelParsedValue = (int)$modelParsedValue;
                    }

                    $walletModel->{$attribute} = $modelParsedValue;
                }

                if ($walletModel instanceof D3pPersonContactCrypto && empty($walletModel->fullType)) {
                    $walletModel->fullType = $this->getFullType($walletModel);
                }

                $saveModel = true;
            }

            if ($saveModel && !$walletModel->save()) {
                //throw new \Exception('Cannot save wallet');
                echo 'Cannot save wallet for: ' . $existingPerson->user->username;
            }

            return true;

        }

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
}
