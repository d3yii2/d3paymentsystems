<?php

use yii\db\Migration;

class m250609_212344_d3yii2_d3paymentsystems_fee_create  extends Migration {

    public function safeUp() { 
        $this->execute('
            CREATE TABLE `d3paymentsystems_fee` (
              `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
              `wallet_sys_model_id` tinyint(3) unsigned NOT NULL COMMENT \'Wallet\',
              `from_country` enum(\'RU\',\'BY\',\'UA\',\'World\') NOT NULL COMMENT \'From country\',
              `from_type` enum(\'True skriller\',\'Skriller\',\'VIP\',\'Main\',\'bnb\') NOT NULL COMMENT \'From Type\',
              `to_country` enum(\'RU\',\'BY\',\'UA\',\'World\') NOT NULL COMMENT \'To country\',
              `to_type` enum(\'True skrill\',\'Skriller\',\'VIP\',\'Main\',\'bnb\') NOT NULL COMMENT \'To Type\',
              `sender_fee` decimal(4,2) unsigned NOT NULL COMMENT \'Sender fee\',
              `receiver_fee` decimal(4,2) unsigned NOT NULL COMMENT \'Receiver fee\',
              PRIMARY KEY (`id`),
              KEY `wallet_sys_model_id` (`wallet_sys_model_id`),
              CONSTRAINT `d3paymentsystems_fee_ibfk_sys_model` FOREIGN KEY (`wallet_sys_model_id`) REFERENCES `sys_models` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4        
        ');
    }

    public function safeDown() {
        echo "m250609_212344_d3yii2_d3paymentsystems_fee_create cannot be reverted.\n";
        return false;
    }
}
