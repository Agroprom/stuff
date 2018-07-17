<?php

namespace frontend\controllers;
use frontend\models\RefinancingForm;
use Yii;

/**
 * Class RefinancingController
 * @package frontend\controllers
 */
class RefinancingController extends Controller
{
    public $layout = 'index';


    public function actionIndex()
    {
        $this->view->params['bottomColumn']='';
        $this->view->registerCssFile('/dist/css/result-block.css');
        $model = new RefinancingForm();
        Yii::$app->seo->setTitle('Проверь ипотеку!');

        if ($model->load(yii::$app->request->post()) && $model->validate()) {
            if ($model->offer < $model->monthlyPayment) {
                return $this->render('success', ['model' => $model]);
            } else {
                return $this->render('failure');
            }
        }


         return $this->render('index', ['model'=> $model]);
    }


}