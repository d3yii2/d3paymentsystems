<?php

namespace d3yii2\d3paymentsystems\models;


use d3modules\d3classifiers\dictionaries\ClCountriesLangDictionary;
use Yii;
use yii2d3\d3persons\models\D3pPersonContact as BaseD3pPersonContact;

/**
 * This is the model class for table "d3p_person_contact".
 */
class D3pPersonContactCrypto extends BaseD3pPersonContact
{

    private const STATUS_ACTUAL = 'ACTUAL';
    private const STATUS_INACTIVE = 'INACTIVE';
    private const STATUS_CLOSED = 'INACTIVE';
    public const STATUS_LISTS = [self::STATUS_ACTUAL, self::STATUS_INACTIVE, self::STATUS_CLOSED];


    public ?string $status = null;
    public ?string $type = null;
    public ?string $subType = null;

    public array $typeDef = [];

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'status' => Yii::t('d3paymentsystems', 'Status'),
                'type' => Yii::t('d3paymentsystems', 'Type'),
                'subType' => Yii::t('d3paymentsystems', 'Subtype'),
                'contact_value' => Yii::t('d3paymentsystems', 'ID'),
            ]

        );
    }

    public function rules(): ?array
    {

        return array_merge(
            parent::rules(),
            [
                [
                    ['type', 'subType', 'contact_value', 'status'],
                    'required',
                ],
                [
                    'status',
                    'in',
                    'range' => self::STATUS_LISTS
                ],
                [
                    'type',
                    'in',
                    'range' => array_keys($this->typeDef),
                    'enableClientValidation' => false
                ],
                [
                    'subType',
                    'validateSubtype',
                ],
                [
                    'contact_value',
                    'match',
                    'pattern' => '/^0x[0-9a-f]{40}$/',
//                    'message' => Yii::t('d3paymentsystems','Phone number format must be like "+123456789"')
//                    'enableClientValidation' => false
                ]
            ]
        );
    }

    public function validateSubtype()
    {
        if (!$this->type) {
            return;
        }
        if (!in_array($this->subType, $this->typeDef[$this->type], true)) {
            $this->addError('type', 'Illegal SubType value');
        }
    }

    public function load($data, $formName = null)
    {
        if (!parent::load($data, $formName)) {
            return false;
        }
        if ($this->type) {
            $list = explode(':', $this->type);
            if (count($list) === 2) {
                [$this->type, $this->subType] = $list;
            }
        }
        return true;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->contact_value = $this->type . ':' .
            $this->subType . ':' .
            $this->contact_value . ':' .
            $this->status;
        return true;
    }

    public function afterFind()
    {
        parent::afterFind();
        [$this->type, $this->subType, $this->contact_value, $this->status] = explode(':', $this->contact_value);
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
