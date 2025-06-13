<?php

use yii\db\Migration;

class m250611_165644_d3yii2_d3paymentsystems_fee_del_to_type  extends Migration {

    public function safeUp() { 
        $this->execute('
            ALTER TABLE `d3paymentsystems_fee` DROP COLUMN `to_type`;         
        ');
    }

    public function safeDown() {
        echo "m250611_165644_d3yii2_d3paymentsystems_fee_del_to_type cannot be reverted.\n";
        return false;
    }
}
