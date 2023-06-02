<?php

namespace d3yii2\d3paymentsystems\components;

use kartik\form\ActiveForm;
use d3yii2\d3paymentsystems\models\D3pPersonContactLuxon;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii2d3\d3persons\components\PersonContactTypeInterface;

class PersonSettingLuxon extends Component implements PersonContactTypeInterface
{


    public ?D3pPersonContactLuxon $model = null;
    public ?int $contactTypeId = null;

    /**
     * @throws InvalidConfigException
     */
    public function inputPersonSettingValue(ActiveForm $form, $model): string
    {
        return $form
                ->field($model, 'fullName')
                ->textInput() .
            $form
                ->field($model, 'contact_value')
                ->textInput() .
            $form
                ->field($model, 'status')
                ->dropDownList(
                    array_combine(
                        D3pPersonContactLuxon::STATUS_LISTS,
                        D3pPersonContactLuxon::STATUS_LISTS
                    ),
                    [
                        'prompt' => Yii::t('d3persons', 'Select')
                    ]
                );
    }

    /**
     * @throws Exception
     */
    public function createNewModel(int $personId)
    {
        if (!$this->contactTypeId) {
            throw new Exception('Undefined contactTypeId');
        }
        $model = new D3pPersonContactLuxon;
        $model->contact_type = $this->contactTypeId;
        $model->person_id = $personId;
        $model->setGroupSettings();
        $model->setStatusActual();
        return $model;
    }

    public function loadModel(array $attributes)
    {
        $model = new D3pPersonContactLuxon();
        $model->setAttributes($attributes);
        $model->setIsNewRecord(false);
        $model->afterFind();
        return $model;
    }
}
