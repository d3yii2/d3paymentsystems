<?php

namespace d3yii2\d3paymentsystems\components;

use d3modules\d3classifiers\dictionaries\ClCountriesLangDictionary;
use kartik\form\ActiveForm;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii2d3\d3persons\components\PersonContactTypeInterface;
use d3yii2\d3paymentsystems\models\D3pPersonContactSkrill;

class PersonSettingSkrill extends Component implements PersonContactTypeInterface
{

    public const CURRENCY_EUR = 'EUR';
    public const CURRENCY_USD = 'USD';
    public const CURRENCY_MULTI = 'MULTI';
    public ?D3pPersonContactSkrill $model = null;
    public array $currencyList = [];
    public ?int $contactTypeId = null;

    /**
     * @throws \Throwable
     */
    public function showValue(array $options = null): string
    {
        return $this->model->currency . ' : ' .
            $this->model->contact_value . ' : ' .
            $this->model->status;
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function inputPersonSettingValue(ActiveForm $form): string
    {
        return $form
                ->field($this->model, 'currency')
                ->dropDownList(
                    array_combine($this->currencyList, $this->currencyList),
                    ['prompt' => Yii::t('d3persons', 'Select')]
                ) .
            $form
                ->field($this->model, 'contact_value')
                ->textInput() .
            $form
                ->field($this->model, 'status')
                ->dropDownList(
                    array_combine(D3pPersonContactSkrill::STATUS_LISTS, D3pPersonContactSkrill::STATUS_LISTS),
                    ['prompt' => Yii::t('d3persons', 'Select')]
                );
    }

    /**
     * @return array
     */
    private function getFlagList(): array
    {
        return ClCountriesLangDictionary::getCodeNameList('en');
    }

    /**
     * @throws \yii\base\Exception
     */
    public function createNewModel(int $personId)
    {
        if (!$this->contactTypeId) {
            throw new Exception('Undefined contactTypeId');
        }
        $this->model = new D3pPersonContactSkrill;
        $this->model->contact_type = $this->contactTypeId;
        $this->model->person_id = $personId;
        $this->model->setGroupSettings();
        $this->model->setStatusActual();
        $this->model->currencyList = $this->currencyList;
        return $this->model;
    }

    public function findModel(int $id)
    {
        $this->model = D3pPersonContactSkrill::findOne($id);
        $this->model->currencyList = $this->currencyList;
        return $this->model;
    }
}
