#!/bin/bash

echo -e '\nImport Skrill'
/usr/bin/php ../../../../yii importWallet/skrill кошельки-Основа.csv 4 5 6 7

echo -e '\nImport Luxon'
/usr/bin/php ../../../../yii importWallet/luxon кошельки-Основа.csv 9 10 11

echo -e '\nImport Crypto'
/usr/bin/php ../../../../yii importWallet/crypto кошельки-Основа.csv 8
