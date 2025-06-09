<?php



use yii\db\Migration;
use d3yii2\d3paymentsystems\accessRights\D3paymentsystemsAdminUserRole;

class m250609_190707_create_roleD3paymentsystemsAdmin  extends Migration {

    public function up() {

        $auth = Yii::$app->authManager;
        $role = $auth->createRole(D3paymentsystemsAdminUserRole::NAME);
        $auth->add($role);

    }

    public function down() {
        $auth = Yii::$app->authManager;
        $role = $auth->createRole(D3paymentsystemsAdminUserRole::NAME);
        $auth->remove($role);
    }
}
