#!/bin/bash

echo -e '\nImport Skrill'
/usr/bin/php ../../../../yii importWallet/skrill кошельки-Элит.csv 4 5 6 7

echo -e '\nImport Luxon'
/usr/bin/php ../../../../yii importWallet/luxon кошельки-Элит.csv 9 10 11

echo -e '\nImport Crypto'
/usr/bin/php ../../../../yii importWallet/crypto кошельки-Элит.csv 8
