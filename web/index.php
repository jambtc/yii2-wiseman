<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

// set env variables for google dialogflow
$project = $config['params']['GOOGLE_CLOUD_PROJECT'];
$credentials = $config['params']['GOOGLE_APPLICATION_CREDENTIALS'];

putenv("GOOGLE_CLOUD_PROJECT=".$project);
putenv("GOOGLE_APPLICATION_CREDENTIALS=".$credentials);

(new yii\web\Application($config))->run();
