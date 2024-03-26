<?php

namespace d3yii2\d3paymentsystems\models;

use d3yii2\d3paymentsystems\dictionaries\CurrenciesDictionary;
use Yii;
use yii\validators\EmailValidator;
use yii2d3\d3persons\models\D3pPersonContact as BaseD3pPersonContact;
use yii2d3\d3persons\models\D3pPersonContactExtInterface;

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
    public ?string $currency = null;

    public array $currencyList = [];



    public function attributeLabels(): array
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'currency' => Yii::t('d3paymentsystems', 'Currency'),
                'status' => Yii::t('d3paymentsystems', 'Status'),
                'fullName' => Yii::t('d3paymentsystems', 'Full Name'),
                'contact_value' => Yii::t('d3paymentsystems', 'Phone or email'),
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
                    ['currency', 'fullName', 'contact_value', 'status'],
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
                    'fullName',
                    'string',
                ],
                [
                    'status',
                    'in',
                    'range' => self::STATUS_LISTS
                ],
                [
                    'contact_value','validateContactValue'
//                    'match',
//                    'pattern' => '/^\+\d{5,15}$/',
//                    'message' => Yii::t('d3paymentsystems', 'Phone number format must be like "+123456789"')
                ]
            ]
        );
    }

    public function validateContactValue(): void
    {
        /** is phone number */
        if (preg_match('/^\+\d{5,15}$/',$this->contact_value)) {
            return;
        }

        $emailValidator = new EmailValidator();
        if ($emailValidator->validate($this->contact_value)) {
            return;
        }
        $this->clearErrors('contact_value');
        $this->addError(
            'contact_value',
            Yii::t('d3paymentsystems','Must be valid email or phone number like "+371444444"')
        );

    }


    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->contact_value = $this->currency . ':' .
            $this->fullName . ':' .
            $this->contact_value . ':' .
            $this->status;
        return true;
    }

    public function afterFind(): void
    {
        parent::afterFind();
        $explode = explode(':', $this->contact_value);
        if (count($explode) === 3) {
            [$this->fullName, $this->contact_value, $this->status] = $explode;
            $this->currency = CurrenciesDictionary::CURRENCY_USD;
        } else {
            [$this->currency, $this->fullName, $this->contact_value, $this->status] = $explode;
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
            $this->fullName . ' ' .
            $this->contact_value . ' : ' .
            $this->status;
    }

    public function showShortContactValue(): string
    {
        return $this->currency . ' : ' .
            $this->fullName . ' ' .
            $this->contact_value;
    }

    public function isCurrencyMulti(): bool
    {
        return $this->currency === CurrenciesDictionary::CURRENCY_MULTI;
    }
}
