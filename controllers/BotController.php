<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;


class BotController extends Controller
{
    
    public function actionIndex()
    {
        $this->layout = 'landing-page';
        return $this->render('index');

    }

}
