<?php

namespace d3yii2\d3paymentsystems\components;

use kartik\form\ActiveForm;
use d3yii2\d3paymentsystems\models\D3pPersonContactCrypto;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii2d3\d3persons\components\PersonContactTypeInterface;

class PersonSettingCrypto extends Component implements PersonContactTypeInterface
{
    public ?D3pPersonContactCrypto $model = null;
    public ?int $contactTypeId = null;
    public array $typeDef = [];


    /**
     * @throws InvalidConfigException
     */
    public function inputPersonSettingValue(ActiveForm $form, $model): string
    {
        return $form
                ->field($model, 'fullType')
                ->dropDownList($model->typeList()) .
            $form
                ->field($model, 'contact_value')
                ->textInput() .
            $form
                ->field($model, 'status')
                ->dropDownList(
                    array_combine(
                        D3pPersonContactCrypto::STATUS_LISTS,
                        D3pPersonContactCrypto::STATUS_LISTS
                    ),
                    [
                        'prompt' => Yii::t('d3persons', 'Select')
                    ]
                );
    }

    /**
     * @param int $personId
     * @return D3pPersonContactCrypto
     * @throws Exception
     */
    public function createNewModel(int $personId)
    {
        if (!$this->contactTypeId) {
            throw new Exception('Undefined contactTypeId');
        }
        $model = new D3pPersonContactCrypto;
        $model->contact_type = $this->contactTypeId;
        $model->person_id = $personId;
        $model->setGroupSettings();
        $model->setStatusActual();
        $model->typeDef = $this->typeDef;
        return $model;
    }

    /**
     * @param array $attributes
     * @return D3pPersonContactCrypto
     */
    public function loadModel(array $attributes)
    {
        $model = new D3pPersonContactCrypto();
        $model->setAttributes($attributes);
        $model->setIsNewRecord(false);
        $model->afterFind();
        $model->typeDef = $this->typeDef;
        return $model;
    }
}
