<?php

namespace d3yii2\d3paymentsystems\components;

use kartik\form\ActiveForm;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii2d3\d3persons\components\PersonContactTypeInterface;
use d3yii2\d3paymentsystems\models\D3pPersonContactSkrill;


class PersonSettingSkrill extends Component implements PersonContactTypeInterface
{
    public const NAME = 'PersonSettingSkrill';
    
    public const CODE = 'W-SKRILL';

    public const CURRENCY_MULTI = 'MULTI';
    public array $currencyList = [];
    public ?int $contactTypeId = null;


    /**
     * @throws InvalidConfigException
     */
    public function inputPersonSettingValue(ActiveForm $form, $model): string
    {
        return $form
                ->field($model, 'currency')
                ->dropDownList(
                    array_combine($model->currencyList, $model->currencyList),
                    ['prompt' => Yii::t('d3persons', 'Select')]
                ) .
            $form
                ->field($model, 'contact_value')
                ->textInput() .
            $form
                ->field($model, 'fee')
                ->textInput() .
            $form
                ->field($model, 'status')
                ->dropDownList(
                    array_combine(D3pPersonContactSkrill::STATUS_LISTS, D3pPersonContactSkrill::STATUS_LISTS),
                    ['prompt' => Yii::t('d3persons', 'Select')]
                );
    }

    /**
     * @param int $personId
     * @return D3pPersonContactSkrill
     * @throws Exception
     */
    public function createNewModel(int $personId)
    {
        if (!$this->contactTypeId) {
            throw new Exception('Undefined contactTypeId');
        }
        $model = new D3pPersonContactSkrill;
        $model->contact_type = $this->contactTypeId;
        $model->person_id = $personId;
        $model->setGroupSettings();
        $model->setStatusActual();
        $model->currencyList = $this->currencyList;
        return $model;
    }

    /**
     * @param array $attributes
     * @return D3pPersonContactSkrill
     */
    public function loadModel(array $attributes)
    {
        $model = new D3pPersonContactSkrill();
        $model->setAttributes($attributes);
        $model->setIsNewRecord(false);
        $model->afterFind();
        $model->currencyList = $this->currencyList;
        return $model;
    }
}
