<?php

namespace d3yii2\d3paymentsystems\models;

use d3yii2\d3paymentsystems\dictionaries\CurrenciesDictionary;
use Yii;
use yii2d3\d3persons\models\D3pPersonContact as BaseD3pPersonContact;

/**
 * This is the model class for table "d3p_person_contact".
 */
class D3pPersonContactLuxon extends BaseD3pPersonContact implements D3pPersonContactExtInterface
{

    private const STATUS_ACTUAL = 'ACTUAL';
    private const STATUS_INACTIVE = 'INACTIVE';
    private const STATUS_CLOSED = 'INACTIVE';
    public const STATUS_LISTS = [self::STATUS_ACTUAL, self::STATUS_INACTIVE, self::STATUS_CLOSED];


    public ?string $status = null;
    public ?string $fullName = null;
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $currency = null;

    public array $currencyList = [];

    public float $fee = 0;
    public float $recipient_fee = 0;

    public function attributeLabels(): array
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'currency' => Yii::t('d3paymentsystems', 'Currency'),
                'status' => Yii::t('d3paymentsystems', 'Status'),
                'fullName' => Yii::t('d3paymentsystems', 'Full Name'),
                'phone' => Yii::t('d3paymentsystems', 'Phone'),
                'email' => Yii::t('d3paymentsystems', 'Mail'),
            ]
        );
    }

    public function rules(): ?array
    {
        return array_merge(
            parent::rules(),
            [
                [['currency', 'fullName', 'status'],'required'],
                ['email','required','when' => static function (self $model) {return !$model->phone;}],
                ['phone','required','when' => static function (self $model) {return !$model->email;}],
                ['currency','in','range' => static function (self $model) {return $model->currencyList;}],
                ['fullName','string',],
                ['email','email'],
                ['phone','validatePhone'],
                ['status','in','range' => self::STATUS_LISTS]
            ]
        );
    }

    public function validatePhone(): void
    {
        /** is phone number */
        if (!preg_match('/^\+\d{5,15}$/',$this->phone)) {
            $this->addError(
                'phone',
                Yii::t('d3paymentsystems','Must be valid phone number like "+371444444"')
            );
        }
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->contact_value = $this->currency . ':' .
            $this->fullName . ':' .
            $this->email . ':' .
            $this->phone . ':' .
            $this->status;
        return true;
    }

    public function afterFind(): void
    {
        parent::afterFind();
        $explode = explode(':', $this->contact_value);
        if (count($explode) === 4) {
            [$this->fullName, $this->email,$this->phone, $this->status] = $explode;
            $this->currency = CurrenciesDictionary::CURRENCY_USD;
        } else {
            [$this->currency, $this->fullName, $this->email,$this->phone, $this->status] = $explode;
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
        $value = [];
        if ($this->email) {
            $value[] = $this->email;
        }
        if ($this->phone) {
            $value[] = $this->phone;
        }
        return $this->currency . ' : ' .
            $this->fullName . ' ' .
            implode(' ', $value) . ' : ' .
            $this->status;
    }

    public function showShortContactValue(): string
    {
        $value = [];
        if ($this->email) {
            $value[] = $this->email;
        }
        if ($this->phone) {
            $value[] = $this->phone;
        }
        return $this->currency . ' : ' .
            $this->fullName . ' ' .
            implode(' ', $value);
    }

    public function isCurrencyMulti(): bool
    {
        return $this->currency === CurrenciesDictionary::CURRENCY_MULTI;
    }
}
