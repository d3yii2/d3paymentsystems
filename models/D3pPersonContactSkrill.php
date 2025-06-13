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
class D3pPersonContactSkrill extends BaseD3pPersonContact implements D3pPersonContactExtInterface
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

    private const TYPE_SKRILLER = D3paymentsystemsFee::FROM_TYPE_SKRILLER;
    private const TYPE_TRUE_SKRILL = D3paymentsystemsFee::FROM_TYPE_TRUE_SKRILL;
    private const TYPE_TRUE_VIP = D3paymentsystemsFee::FROM_TYPE_VIP;
    public const TYPE_LIST = [self::TYPE_TRUE_SKRILL, self::TYPE_TRUE_VIP, self::TYPE_SKRILLER];


    public ?string $currency = null;
    public ?string $status = null;
    public ?string $country = null;
    public ?string $type = null;

    public array $currencyList = [];

    public function attributeLabels(): array
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'currency' => Yii::t('d3paymentsystems', 'Currency'),
                'status' => Yii::t('d3paymentsystems', 'Status'),
                'contact_value' => Yii::t('d3paymentsystems', 'Account'),
                'type' => Yii::t('d3paymentsystems', 'Type'),
                'country' => Yii::t('d3paymentsystems', 'Country'),
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
                    ['currency', 'contact_value', 'status', 'type', 'country'],
                    'required',
                ],
                [
                    'currency',
                    'in',
                    'range' => static function () use ($me){
                        return $me->currencyList;
                    }
                ],
                [['type','country'], 'string'],
                [
                    'type',
                    'in',
                    'range' => self::TYPE_LIST
                ],
                [
                    'country',
                    'in',
                    'range' => self::COUNTRY_LISTS
                ],
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
        $this->createContactValue();
        return true;
    }

    public function afterFind(): void
    {
        parent::afterFind();
        $explode = explode(':', $this->contact_value);
        $this->status = self::STATUS_ACTUAL;
        $this->country = null;
        $this->type = null;
        if (count($explode) === 1) {
            $this->currency = CurrenciesDictionary::CURRENCY_MULTI;
        } elseif (count($explode) === 2) {
            [$this->currency, $this->contact_value, $this->status] = $explode;
        } elseif (count($explode) === 4) {
            [$this->currency, $this->contact_value, $this->status,$this->country] = $explode;
        } elseif (count($explode) === 5) {
            [$this->currency, $this->contact_value, $this->status, $this->country, $this->type] = $explode;
            if (!in_array( $this->country, self::COUNTRY_LISTS, true)) {
                $this->country = null;
                $this->type = null;
            }
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

    public function getFeeLabel(): string
    {
        $value = [];
        if ($this->country) {
            $value[] = Yii::t('d3paymentsystems', 'Country') . ': ' . $this->country;
        }
        if ($this->type) {
            $value[] = Yii::t('d3paymentsystems', 'Type') . ': '  . $this->type;
        }
        return implode(' ', $value);
    }

    public function getWalletCode(): string
    {
        return $this->contact_value;
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

    /**
     * @return void
     */
    public function createContactValue(): void
    {
        $this->contact_value = $this->currency . ':' .
            $this->contact_value . ':' .
            $this->status . ':' .
            $this->country . ':' .
            $this->type;
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
