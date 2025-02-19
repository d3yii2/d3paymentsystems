<?php

namespace d3yii2\d3paymentsystems\models;


use d3system\models\ModelHelper;
use d3yii2\d3paymentsystems\dictionaries\CurrenciesDictionary;
use Yii;
use yii\base\InvalidConfigException;
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
    private const FLOAT_ATTRIBUTES = ['fee', 'fee_amount', 'recipient_fee', 'recipient_fee_amount'];


    public ?string $currency = null;
    public ?string $status = null;
    public float $fee = 0;
    public float $recipient_fee = 0;
    public float $fee_amount = 0;
    public float $recipient_fee_amount = 0;

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
                [self::FLOAT_ATTRIBUTES,'number'],
                [
                    'contact_value',
                    'email'
                ]
            ]
        );
    }

    /**
     * @throws InvalidConfigException
     */
    public function load($data, $formName = null): bool
    {
        $scope = $formName ?? $this->formName();
        $attributes = &$data[$scope];
        ModelHelper::normalizeFloatDataAttributes(self::FLOAT_ATTRIBUTES, $attributes);
        return parent::load($data, $formName);
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
        $this->fee = 0;
        $this->recipient_fee = 0;
        $this->fee_amount = 0;
        $this->recipient_fee_amount = 0;
        if (count($explode) === 1) {
            $this->currency = CurrenciesDictionary::CURRENCY_MULTI;
        } elseif (count($explode) === 2) {
            [$this->currency, $this->contact_value, $this->status] = $explode;
        } elseif (count($explode) === 4) {
            [$this->currency, $this->contact_value, $this->status,$this->fee] = $explode;
        } elseif (count($explode) === 5) {
            [$this->currency, $this->contact_value, $this->status, $this->fee, $this->recipient_fee] = $explode;
        } else {
            [
                $this->currency,
                $this->contact_value,
                $this->status,
                $this->fee,
                $this->recipient_fee,
                $this->fee_amount,
                $this->recipient_fee_amount
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
        return $this->currency . ' : ' .
            $this->contact_value . ' : ' .
            $this->status;
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

    public function showShortContactValue(): string
    {
        return $this->currency . ' : ' .
            $this->contact_value;
    }

    public function isCurrencyMulti(): bool
    {
        return $this->currency === CurrenciesDictionary::CURRENCY_MULTI;
    }

    public function calcFee(float $amount)
    {
        if ($this->fee_amount) {
            return $this->fee_amount;
        }
        if ($this->fee) {
            return  round($amount * ($this->fee / 100.), 2);
        }
        return 0;
    }

    public function calcRecipientFee(float $amount)
    {
        if ($this->recipient_fee_amount) {
            return $this->recipient_fee_amount;
        }
        if ($this->recipient_fee) {
            return  round($amount * ($this->recipient_fee / 100.), 2);
        }
        return 0;
    }

    /**
     * @return void
     */
    public function createContactValue(): void
    {
        $this->contact_value = $this->currency . ':' .
            $this->contact_value . ':' .
            $this->status . ':' .
            $this->fee . ':' .
            $this->recipient_fee . ':' .
            $this->fee_amount . ':' .
            $this->recipient_fee_amount;
    }
}
