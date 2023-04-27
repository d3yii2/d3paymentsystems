<?php

namespace d3yii2\d3paymentsystems\components;

use kartik\form\ActiveForm;
use d3yii2\d3paymentsystems\models\D3pPersonContactCrypto;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii2d3\d3persons\components\PersonContactTypeInterface;

class PersonSettingCrypto extends Component implements PersonContactTypeInterface
{


    public ?D3pPersonContactCrypto $model = null;
    public ?int $contactTypeId = null;
    public array $typeDef = [];

    /**
     * @throws \Throwable
     */
    public function showValue(array $options = null): string
    {
        return $this->model->type . ' ' .
            '(' . $this->model->subType . '): ' .
            $this->model->contact_value . ' : ' .
            $this->model->status;
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function inputPersonSettingValue(ActiveForm $form): string
    {
        $list = [];
        foreach ($this->typeDef as $type => $subTypes) {
            foreach ($subTypes as $subType) {
                $list[$type . ':' . $subType] = $type . ' (' . $subType . ')';
            }
        }
        $typeList = array_keys($this->typeDef);
        return $form
                ->field($this->model, 'type')
                ->dropDownList($list) .
            $form
                ->field($this->model, 'contact_value')
                ->textInput() .
            $form
                ->field($this->model, 'status')
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
     * @throws \yii\base\Exception
     */
    public function createNewModel(int $personId)
    {
        if (!$this->contactTypeId) {
            throw new Exception('Undefined contactTypeId');
        }
        $this->model = new D3pPersonContactCrypto;
        $this->model->contact_type = $this->contactTypeId;
        $this->model->person_id = $personId;
        $this->model->setGroupSettings();
        $this->model->setStatusActual();
        $this->model->typeDef = $this->typeDef;
        return $this->model;
    }

    public function findModel(int $id)
    {
        $this->model = D3pPersonContactCrypto::findOne($id);
        $this->model->typeDef = $this->typeDef;
        return $this->model;
    }
}
