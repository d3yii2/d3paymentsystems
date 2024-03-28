<?php

namespace d3yii2\d3paymentsystems\models;


use d3yii2\d3paymentsystems\dictionaries\CurrenciesDictionary;
use Yii;
use yii2d3\d3persons\models\D3pPersonContact as BaseD3pPersonContact;

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
    public float $fee = 0;

    public array $currencyList = [];

    public function attributeLabels(): array
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'currency' => Yii::t('d3paymentsystems', 'Currency'),
                'status' => Yii::t('d3paymentsystems', 'Status'),
                'contact_value' => Yii::t('d3paymentsystems', 'Account'),
                'fee' => Yii::t('d3paymentsystems', 'Fee%'),
            ]

        );
    }

    public function rules(): ?array
    {
        $me = $this;
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
                    'range' => static function () use ($me){
                        return $me->currencyList;
                    }
                ],
                [
                    'status',
                    'in',
                    'range' => self::STATUS_LISTS
                ],
                ['fee','number'],
                [
                    'contact_value',
                    'email'
                ]
            ]
        );
    }


    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->contact_value = $this->currency . ':' .
            $this->contact_value . ':' .
            $this->status . ':' .
            $this->fee
        ;
        return true;
    }

    public function afterFind(): void
    {
        parent::afterFind();
        $explode = explode(':', $this->contact_value);
        if (count($explode) === 1) {
            $this->currency = CurrenciesDictionary::CURRENCY_MULTI;
            $this->status = self::STATUS_ACTUAL;
            $this->fee = 0;
        } elseif (count($explode) === 2) {
            [$this->currency, $this->contact_value, $this->status] = $explode;
            $this->fee = 0;
        } else {
            [$this->currency, $this->contact_value, $this->status, $this->fee] = $explode;
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
        $value = $this->currency . ' : ' .
            $this->contact_value . ' : ' .
            $this->status;
        if ($this->fee) {
            $value .= ' ' . $this->fee . '%';
        }
        return $value;
    }

    public function showShortContactValue(): string
    {
        return $this->currency . ' : ' .
            $this->contact_value;
    }

    public function isCurrencyMulti(): bool
    {
        return $this->currency === CurrenciesDictionary::CURRENCY_MULTI;
    }
}
