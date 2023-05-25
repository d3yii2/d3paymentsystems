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
    public function inputPersonSettingValue(ActiveForm $form): string
    {
        return $form
                ->field($this->model, 'fullName')
                ->textInput() .
            $form
                ->field($this->model, 'contact_value')
                ->textInput() .
            $form
                ->field($this->model, 'status')
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
        $this->model = new D3pPersonContactLuxon;
        $this->model->contact_type = $this->contactTypeId;
        $this->model->person_id = $personId;
        $this->model->setGroupSettings();
        $this->model->setStatusActual();
        return $this->model;
    }

    public function findModel(int $id)
    {
        $this->model = D3pPersonContactLuxon::findOne($id);
        return $this->model;
    }
}
