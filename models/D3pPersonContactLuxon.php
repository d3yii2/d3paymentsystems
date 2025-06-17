<?php

namespace d3yii2\d3paymentsystems\models;

use d3yii2\d3paymentsystems\dictionaries\CurrenciesDictionary;
use Yii;
use yii2d3\d3persons\models\D3pPersonContact as BaseD3pPersonContact;

/**
 * This is the model class for table "d3p_person_contact".
 *
 * @property-read string $feeLabel
 */
class D3pPersonContactLuxon extends BaseD3pPersonContact implements D3pPersonContactExtInterface
{

    private const STATUS_ACTUAL = 'ACTUAL';
    private const STATUS_INACTIVE = 'INACTIVE';
    private const STATUS_CLOSED = 'INACTIVE';
    public const STATUS_LISTS = [self::STATUS_ACTUAL, self::STATUS_INACTIVE, self::STATUS_CLOSED];

    private const COUNTRY_RU = D3paymentsystemsFee::TO_COUNTRY_RU;
    private const COUNTRY_UA = D3paymentsystemsFee::TO_COUNTRY_UA;
    private const COUNTRY_BLR = D3paymentsystemsFee::TO_COUNTRY_BLR;
    private const COUNTRY_WORLD = D3paymentsystemsFee::TO_COUNTRY_WORLD;
    public const COUNTRY_LISTS = [self::COUNTRY_RU, self::COUNTRY_UA, self::COUNTRY_BLR, self::COUNTRY_WORLD];

    private const TYPE_MAIN = D3paymentsystemsFee::FROM_TYPE_MAIN;
    public const TYPE_LIST = [self::TYPE_MAIN];

    public ?string $status = null;
    public ?string $fullName = null;
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $currency = null;
    public ?string $country = null;
    public ?string $type = null;

    public array $currencyList = [];

    public function init()
    {
        parent::init();
        $this->type = self::TYPE_MAIN;
    }

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
                'type' => Yii::t('d3paymentsystems', 'Type'),
                'country' => Yii::t('d3paymentsystems', 'Country'),
            ]
        );
    }

    public function rules(): ?array
    {
        return array_merge(
            parent::rules(),
            [
                [['currency', 'status','email','type'],'required'],
                ['currency','in','range' => static function (self $model) {return $model->currencyList;}],
                [['type','country'], 'string'],
                ['type', 'in', 'range' => self::TYPE_LIST],
                ['country', 'in', 'range' => self::COUNTRY_LISTS],
                ['fullName','string',],
                ['email','email'],
                ['phone','validatePhone'],
                ['status','in','range' => self::STATUS_LISTS],
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

    public function validateEmail(): void
    {
        if (
            self::find()
            ->where([
                'like',
                'contact_value',
                $this->email
            ])
            ->andWhere([
                'contact_type' => $this->contact_type
            ])
            ->one()
        ) {
            $this->addError('email', Yii::t('d3paymentsystems', 'Email already used other Luxon wallet'));
        }
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->createContactValue();
        return true;
    }

    public function afterFind(): void
    {
        parent::afterFind();
        $this->status = self::STATUS_ACTUAL;
        $this->country = null;
        $this->type = self::TYPE_MAIN;
        $explode = explode(':', $this->contact_value);
        if (count($explode) === 4) {
            [$this->fullName, $this->email,$this->phone, $this->status] = $explode;
            $this->currency = CurrenciesDictionary::CURRENCY_USD;
        } elseif (count($explode) === 5)  {
            [$this->currency, $this->fullName, $this->email,$this->phone, $this->status] = $explode;
        } elseif (count($explode) === 7)  {
            [
                $this->currency, $this->fullName, $this->email,
                $this->phone, $this->status, $this->country,
                $this->type
            ] = $explode;
        } elseif (count($explode) === 9)  {
            [
                $this->currency, $this->fullName, $this->email,$this->phone, $this->status,
                $fee, $recipient_fee, $fee_amount, $recipient_fee_amount
            ] = $explode;
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

    public function getFeeLabel(): string
    {
        $value = [];
        $value[] = Yii::t('d3paymentsystems', 'Country') . ': ' . $this->country;
        $value[] = Yii::t('d3paymentsystems', 'Type') . ': '  . $this->type;
        return implode(' ', $value);
    }

    public function getWalletCode(): string
    {
        return $this->email;
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

    /**
     * @return void
     */
    public function createContactValue(): void
    {
        $this->contact_value = $this->currency . ':' .
            $this->fullName . ':' .
            $this->email . ':' .
            $this->phone . ':' .
            $this->status . ':' .
            $this->country . ':' .
            self::TYPE_MAIN;
    }

    public static function optsCountry()
    {
        return array_combine(self::COUNTRY_LISTS, self::COUNTRY_LISTS);
    }

    public static function optsType()
    {
        return array_combine(self::TYPE_LIST, self::TYPE_LIST);
    }
}
