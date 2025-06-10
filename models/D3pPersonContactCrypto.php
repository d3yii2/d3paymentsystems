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
class D3pPersonContactCrypto extends BaseD3pPersonContact implements D3pPersonContactExtInterface
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

    private const TYPE_BNB = D3paymentsystemsFee::TO_TYPE_BNB;
    public const TYPE_LIST = [self::TYPE_BNB];

    public ?string $status = null;
    public ?string $type = null;
    public ?string $subType = null;
    public ?string $fullType = null;
    public ?string $country = null;

    public array $typeDef = [];
    public ?string $currency = null;

    public function init(): void
    {
        parent::init();
        $this->currency = CurrenciesDictionary::CURRENCY_MULTI;
    }

    public function attributeLabels(): array
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'status' => Yii::t('d3paymentsystems', 'Status'),
                'fullType' => Yii::t('d3paymentsystems', 'Type'),
                'type' => Yii::t('d3paymentsystems', 'Type'),
                'subType' => Yii::t('d3paymentsystems', 'Subtype'),
                'contact_value' => Yii::t('d3paymentsystems', 'ID'),
                'fee' => Yii::t('d3paymentsystems', 'Fee%'),
                'fee_amount' => Yii::t('d3paymentsystems', 'Fee amount'),
                'recipient_fee' => Yii::t('d3paymentsystems', 'Recipient fee%'),
                'recipient_fee_amount' => Yii::t('d3paymentsystems', 'Recipient fee amount'),
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
                    [
                        'type', 'subType', 'contact_value', 'status', 'country'
                    ],
                    'required',
                ],
                ['fullType', 'string'],
                [
                    'status',
                    'in',
                    'range' => self::STATUS_LISTS
                ],
                [
                    'type',
                    'in',
                    'range' => static function () use ($me) {
                        return array_keys($me->typeDef);
                    }
                ],
                [
                    'subType',
                    'in',
                    'range' => static function () use ($me) {
                        return $me->subTypeKeys();
                    }
                ],
                [
                    'contact_value',
                    'match',
                    'pattern' => '/^0x[0-9a-f]{40}$/',
                ],
                [
                    'country',
                    'in',
                    'range' => self::COUNTRY_LISTS
                ],
            ]
        );
    }

    public function load($data, $formName = null): bool
    {
        if (!parent::load($data, $formName)) {
            return false;
        }
        if ($this->fullType) {
            $list = explode(':', $this->fullType);
            if (count($list) === 2) {
                [$this->type, $this->subType] = $list;
            }
        }
        return true;
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->createContactValue();
        return true;
    }

    public function typeList(): array
    {
        $list = [];
        foreach ($this->typeDef as $type => $subTypes) {
            foreach ($subTypes as $subType) {
                $list[$type . ':' . $subType] = $type . ' (' . $subType . ')';
            }
        }
        return $list;
    }

    public function subTypeKeys(): array
    {
        $list = [];
        foreach ($this->typeDef as $subTypes) {
            foreach ($subTypes as $subType) {
                $list[] = $subType;
            }
        }
        return $list;
    }

    public function afterFind(): void
    {
        parent::afterFind();
//        [$this->type, $this->subType, $this->contact_value, $this->status] = explode(':', $this->contact_value);
        $this->country = null;
        $explode = explode(':', $this->contact_value);
        if (count($explode) === 4) {
            [$this->type, $this->subType, $this->contact_value, $this->status] = $explode;
        } elseif (count($explode) === 8) {
            [
                $this->type,
                $this->subType,
                $this->contact_value,
                $this->status,
                $fee,
                $recipient_fee,
                $fee_amount,
                $recipient_fee_amount
            ] = $explode;
        } else {
            [
                $this->type,
                $this->subType,
                $this->contact_value,
                $this->status,
                $this->country
            ] = $explode;

        }

    }

    public function showContactValue(array $options = null): string
    {
        return $this->type . ' ' .
            '(' . $this->subType . '): ' .
            $this->contact_value . ' : ' .
            $this->status;
    }

    public function showShortContactValue(array $options = null): string
    {
        return $this->type . ' ' .
            '(' . $this->subType . '): ' .
            $this->contact_value;
    }

    public function getFeeLabel(): string
    {
        return Yii::t('d3paymentsystems', 'Country') . ': ' . $this->country;
    }

    public function getWalletCode(): string
    {
        return $this->contact_value;
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

    public function isCurrencyMulti(): bool
    {
        return $this->currency === CurrenciesDictionary::CURRENCY_MULTI;
    }

    /**
     * @return void
     */
    public function createContactValue(): void
    {
        $this->contact_value = $this->type . ':' .
            $this->subType . ':' .
            $this->contact_value . ':' .
            $this->status . ':' .
            $this->country;
    }

    public static function optsCountry()
    {
        return array_combine(self::COUNTRY_LISTS, self::COUNTRY_LISTS);
    }

}
