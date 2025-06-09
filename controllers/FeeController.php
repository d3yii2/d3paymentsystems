<?php

namespace d3yii2\d3paymentsystems\controllers;

use d3yii2\d3paymentsystems\models\D3paymentsystemsFee;
use d3yii2\d3paymentsystems\models\D3paymentsystemsFeeSearch;
use yii\web\Controller;
use Yii;
use Exception;
use d3system\helpers\FlashHelper;
use Throwable;
use yii\base\UserException;
use yii\web\HttpException;
use yii\web\Response;
use yii\filters\AccessControl;
use d3yii2\d3paymentsystems\Module;
use d3yii2\d3paymentsystems\accessRights\D3paymentsystemsAdminUserRole;
/**
* FeeController implements the CRUD actions for D3paymentsystemsFee model.
* @property Module $module
*/
class FeeController extends Controller
{
    /**
    * @var boolean whether to enable CSRF validation for the actions in this controller.
    * CSRF validation is enabled only when both this property and [[Request::enableCsrfValidation]] are true.
    */
    public $enableCsrfValidation = false;

    /**
    * specify route for identifying active menu item
    */
    public $menuRoute = 'd3payments/fee/index';


    /**
    * @inheritdoc
    */
    public function behaviors(): array
    {
    return [
        'access' => [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => [
                        'index',
                        'create',
                        'update',
                        'delete',
                   ],
                   'roles' => [
                        D3paymentsystemsAdminUserRole::NAME                   ],
                ],
            ],
         ],
        ];
    }

    /**
    * Lists all D3paymentsystemsFee models.
    * @return string
    */
    public function actionIndex(): string
    {
        $searchModel  = new D3paymentsystemsFeeSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get());
        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    /**
    * Creates a new D3paymentsystemsFee model.
    * If creation is successful, the browser will be redirected
    *  to the 'view' page or back, if parameter $goBack is true.
    * @return string|Response
    * @throws \yii\db\Exception
    */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new D3paymentsystemsFee;
        if (!$transaction = Yii::$app->getDb()->beginTransaction()) {
            throw new \yii\db\Exception('Can not initiate transaction');
        }
        try {
            if ($model->load($request->post()) && $model->save()) {
                $transaction->commit();
                return $this->redirect(['index']);
            }
            $transaction->rollback();
        } catch (Exception $e) {
            $transaction->rollback();
            FlashHelper::processException($e);
        }
        return $this->render(
            'create',
            [
                'model' => $model,
            ]
        );
    }

    /**
    * Updates an existing D3paymentsystemsFee model.
    * If the update is successful, the browser will be redirected to the 'view' page.
    * @param integer $id
    * @return string|Response
    * @throws \yii\db\Exception|HttpException
     */
    public function actionUpdate(int $id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        if (!$transaction = Yii::$app->getDb()->beginTransaction()) {
            throw new \yii\db\Exception('Can not initiate transaction');
        }
        try {
            if ($model->load($request->post()) && $model->save()) {
                $transaction->commit();
                return $this->redirect(['index']);
            }
            $transaction->rollback();
        } catch (Exception $e) {
            $transaction->rollback();
            FlashHelper::processException($e);
        }
        return $this->render(
            'update',
            [
                'model' => $model,
            ]
        );
    }

    /**
    * Deletes an existing D3paymentsystemsFee model.
    * If deletion is successful, the browser will be redirected to the 'index' page.
    * @param integer $id
    * @return Response
    * @throws Throwable
    */
    public function actionDelete(int $id): Response
    {
        if (!$transaction = Yii::$app->getDb()->beginTransaction()) {
            throw new \yii\db\Exception('Can not initiate transaction');
        }
        try {
            $this->findModel($id)->delete();
            $transaction->commit();
        } catch (UserException $e) {
            $transaction->rollback();
            FlashHelper::addDanger($e->getMessage());
            return $this->redirect(['view', 'id' => $id]);
        } catch (Exception $e) {
            $transaction->rollback();
            FlashHelper::processException($e);
            return $this->redirect(['view', 'id' => $id]);
        }
        return $this->redirect(['index']);
    }


    /**
    * Finds the D3paymentsystemsFee model based on its primary key value.
    * If the model is not found, a 404 HTTP exception will be thrown.
    * @param integer $id
    * @return D3paymentsystemsFee the loaded model
    * @throws HttpException if the model cannot be found
    */
    public function findModel(int $id): D3paymentsystemsFee
    {
        return D3paymentsystemsFee::findForController($id);
    }
}
