<?php

namespace d3yii2\d3paymentsystems\models;

use d3yii2\d3paymentsystems\components\PersonSettingSkrill;
use Yii;
use yii2d3\d3persons\models\D3pPersonContact as BaseD3pPersonContact;
use yii2d3\d3persons\models\D3pPersonContactExtInterface;

/**
 * This is the model class for table "d3p_person_contact".
 */
class D3pPersonContactSkrill extends BaseD3pPersonContact implements D3pPersonContactExtInterface
{

    private const STATUS_ACTUAL = 'ACTUAL';
    private const STATUS_INACTIVE = 'INACTIVE';
    private const STATUS_CLOSED = 'INACTIVE';
    public const STATUS_LISTS = [self::STATUS_ACTUAL, self::STATUS_INACTIVE, self::STATUS_CLOSED];


    public ?string $currency = null;
    public ?string $status = null;

    public array $currencyList = [];

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'currency' => Yii::t('d3paymentsystems', 'Currency'),
                'status' => Yii::t('d3paymentsystems', 'Status'),
                'contact_value' => Yii::t('d3paymentsystems', 'Account'),
            ]

        );
    }

    public function rules(): ?array
    {

        return array_merge(
            parent::rules(),
            [
                [
                    ['currency', 'contact_value', 'status'],
                    'required',
                ],
                [
                    'currency',
                    'in',
                    'range' => $this->currencyList
                ],
                [
                    'status',
                    'in',
                    'range' => self::STATUS_LISTS
                ],
                [
                    'contact_value',
                    'email'
                ]
            ]
        );
    }


    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->contact_value = $this->currency . ':' .
            $this->contact_value . ':' .
            $this->status;
        return true;
    }

    public function afterFind()
    {
        parent::afterFind();
        $explode = explode(':', $this->contact_value);
        if (count($explode) === 1) {
            $this->currency = PersonSettingSkrill::CURRENCY_MULTI;
            $this->status = self::STATUS_ACTUAL;
        } else {
            [$this->currency, $this->contact_value, $this->status] = $explode;
        }
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


    public function showContactValue(): string
    {
        return $this->currency . ' : ' .
            $this->contact_value . ' : ' .
            $this->status;
    }

    public function showShortContactValue(): string
    {
        return $this->currency . ' : ' .
            $this->contact_value;
    }
}
