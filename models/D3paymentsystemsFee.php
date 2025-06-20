<?php

namespace d3yii2\d3paymentsystems\models;

use d3system\dictionaries\SysModelsDictionary;
use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3paymentsystems\models\base\D3paymentsystemsFee as BaseD3paymentsystemsFee;
use yii\web\HttpException;

/**
 * This is the model class for table "d3paymentsystems_fee".
 */
class D3paymentsystemsFee extends BaseD3paymentsystemsFee
{
    /**
     * @throws HttpException
     */
    public static function findForController(int $id): self
    {
        if (($model = self::findOne($id)) === null) {
            throw new HttpException(404, 'The requested page does not exist.');
        }
        return $model;
    }

    /**
     * @throws D3ActiveRecordException
     */
    public static function optsWalletSysModelId(): array
    {
        return [
            SysModelsDictionary::getIdByClassName(D3pPersonContactCrypto::class) => 'Crypto',
            SysModelsDictionary::getIdByClassName(D3pPersonContactLuxon::class) => 'Luxon',
            SysModelsDictionary::getIdByClassName(D3pPersonContactSkrill::class) => 'Skrill',
        ];
    }

    /**
     * @throws D3ActiveRecordException
     */
    public static function findFromToCountryRow(object $from, string $toCountry): ?self
    {
        return self::findOne([
            'wallet_sys_model_id' => SysModelsDictionary::getIdByClassName(get_class($from)),
            'from_country' => $from->country,
            'to_country' => $toCountry,
            'from_type' => $from->type
        ]);
    }

    public function calcSenderFee(float $amount): float
    {
        if ((float)$this->sender_fee === 0.) {
            return 0.;
        }
        return  round($amount * ($this->sender_fee / 100.), 2);
    }

    public function calcReceiverFee(float $amount): float
    {
        if ((float)$this->receiver_fee === 0.) {
            return 0.;
        }
        return  round($amount * ($this->receiver_fee / 100.), 2);

    }

    public function calcTotalFee(float $amount): float
    {
        $fee = (float)$this->receiver_fee + (float)$this->sender_fee;
        if ($fee === 0.) {
            return 0.;
        }
        return  round($amount * ($fee / 100.), 2);

    }

    public function addNotesSenderFeeFond(float $tranAmount, string $notes = null): ?string
    {
        if (!$fee = $this->calcSenderFee($tranAmount)) {
            return $notes;
        }
        $notesList = ['Fee fond: ' . $fee];
        if ($formNotes = trim($notes)) {
            $notesList[] = $formNotes;
        }
        return implode('; ', $notesList);
    }

    public function addNotesTotalFeeFond(float $tranAmount, string $notes = null): ?string
    {
        if (!$fee = $this->calcTotalFee($tranAmount)) {
            return $notes;
        }
        $notesList = ['Fee fond: ' . $fee];
        if ($formNotes = trim($notes)) {
            $notesList[] = $formNotes;
        }
        return implode('; ', $notesList);
    }

    public function addNotesReceiverFee(float $tranAmount, string $notes = null): ?string
    {
        if (!$fee = $this->calcReceiverFee($tranAmount)) {
            return $notes;
        }
        $notesList = ['Fee recipient: ' . $fee];
        if ($formNotes = trim($notes)) {
            $notesList[] = $formNotes;
        }
        return implode('; ', $notesList);
    }

    public static function getFondFee(string $notes): float
    {
        if (preg_match('#Fee fond: (\d+\.?\d*)#',$notes, $match)) {
            return (float)$match[1];
        }
        return 0.;
    }

    public static function getRecipientFee(string $notes): float
    {
        if (preg_match('#Fee recipient: (\d+\.?\d*)#',$notes, $match)) {
            return (float)$match[1];
        }
        return 0.;
    }

}

