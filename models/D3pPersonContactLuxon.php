<?php

namespace d3yii2\d3paymentsystems\models;


use d3modules\d3classifiers\dictionaries\ClCountriesLangDictionary;
use Yii;
use yii2d3\d3persons\models\D3pPersonContact as BaseD3pPersonContact;

/**
 * This is the model class for table "d3p_person_contact".
 */
class D3pPersonContactLuxon extends BaseD3pPersonContact
{

    private const STATUS_ACTUAL = 'ACTUAL';
    private const STATUS_INACTIVE = 'INACTIVE';
    private const STATUS_CLOSED = 'INACTIVE';
    public const STATUS_LISTS = [self::STATUS_ACTUAL, self::STATUS_INACTIVE, self::STATUS_CLOSED];


    public ?string $status = null;
    public ?string $fullName = null;

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'status' => Yii::t('d3paymentsystems', 'Status'),
                'fullName' => Yii::t('d3paymentsystems', 'Full Name'),
                'contact_value' => Yii::t('d3paymentsystems', 'Phone'),
            ]

        );
    }

    public function rules(): ?array
    {

        return array_merge(
            parent::rules(),
            [
                [
                    ['fullName', 'contact_value', 'status'],
                    'required',
                ],
                [
                    'fullName',
                    'string',
                ],
                [
                    'status',
                    'in',
                    'range' => self::STATUS_LISTS
                ],
                [
                    'contact_value',
                    'match',
                    'pattern' => '/^\+\d{5,15}$/',
                    'message' => Yii::t('d3paymentsystems', 'Phone number format must be like "+123456789"')
                ]
            ]
        );
    }


    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->contact_value = $this->fullName . ':' .
            $this->contact_value . ':' .
            $this->status;
        return true;
    }

    public function afterFind()
    {
        parent::afterFind();
        [$this->fullName, $this->contact_value, $this->status] = explode(':', $this->contact_value);
    }

    public function setStatusActual(): void
    {
        $this->status = self::STATUS_ACTUAL;
    }

    public function setStatusInactive(): void
    {
        $this->status = self::STATUS_INACTIVE;
    }

    public function setStatusClosed(): void
    {
        $this->status = self::STATUS_CLOSED;
    }

    public function isStatusActual(): bool
    {
        return $this->status === self::STATUS_ACTUAL;
    }

    public function isStatusInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    public function isStatusClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }
}
