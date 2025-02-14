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


    public ?string $status = null;
    public ?string $type = null;
    public ?string $subType = null;

    public ?string $fullType = null;

    public array $typeDef = [];
    public ?string $currency = null;

    public float $fee = 0;
    public float $recipient_fee = 0;
    public float $fee_amount = 0;
    public float $recipient_fee_amount = 0;

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
                        'type', 'subType', 'contact_value', 'status'
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
//                    'message' => Yii::t('d3paymentsystems','Phone number format must be like "+123456789"')
//                    'enableClientValidation' => false
                ],
                [['fee','fee_amount','recipient_fee','recipient_fee_amount'],'number'],
            ]
        );
    }



    public function load($data, $formName = null): bool
    {
        if (!parent::load($data, $formName)) {
            return false;
        }
        foreach (['fee','fee_amount','recipient_fee','recipient_fee_amount'] as $attributeName) {
            if (!$this->$attributeName) {
                $this->$attributeName = 0;
            }
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
        $this->fee = 0;
        $this->recipient_fee = 0;
        $this->fee_amount = 0;
        $this->recipient_fee_amount = 0;
        $explode = explode(':', $this->contact_value);
        if (count($explode) === 4) {
            [$this->type, $this->subType, $this->contact_value, $this->status] = $explode;
        } else {
            [
                $this->type,
                $this->subType,
                $this->contact_value,
                $this->status,
                $this->fee,
                $this->recipient_fee,
                $this->fee_amount,
                $this->recipient_fee_amount
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
        $value = [];
        if ($this->fee) {
            $value[] = Yii::t('d3paymentsystems', 'Payer') . ': ' . $this->fee . '%';
        }
        if ($this->recipient_fee) {
            $value[] = Yii::t('d3paymentsystems', 'Receiver') . ': '  . $this->recipient_fee . '%';
        }
        if ($this->fee_amount) {
            $value[]  = Yii::t('d3paymentsystems', 'Payer') . ': ' . $this->fee_amount;
        }
        if ($this->recipient_fee_amount) {
            $value[] = Yii::t('d3paymentsystems', 'Receiver') . ': ' . $this->recipient_fee_amount;
        }
        return implode(' ', $value);
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
            $this->fee . ':' .
            $this->recipient_fee . ':' .
            $this->fee_amount . ':' .
            $this->recipient_fee_amount . ':';
    }
}
