<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;

class WisemanController extends Controller
{
    public function beforeAction($action)
	{
    	$this->enableCsrfValidation = false;
    	return parent::beforeAction($action);
	}

    public function actionIndex()
    {

        // Set your driver
        DriverManager::loadDriver(\BotMan\Drivers\Web\WebDriver::class);

        // config web driver
        $config = [
            'web' => [
            	'matchingData' => [
                    'driver' => 'web',
                ],
            ]
        ];

        // Create BotMan instance
        $botman = BotManFactory::create($config, new \idk\yii2\botman\Cache());

        $botman->fallback(function ($bot) {
            $message = $bot->getMessage();
            $value = $message->getText();

            if (trim($value) !== ''){
                //$timestamp = $bot->getMessage()->getExtras('timestamp');

                //$bot->reply($timestamp .' : Non capisco');
                $bot->reply('Prova a digitare "help"');
            }
        });



        $botman->hears('hello', function(BotMan $bot) {
            $bot->reply('world');
        });


        $botman->hears('come ti chiami(.*)', function (BotMan $bot) {
            $bot->reply('Il mio nome è Wiseman, che significa "Uomo saggio"');
        });

        $botman->hears('Hi|Hello|Ciao', function (BotMan $bot) {
            $bot->reply('Eccomi!');
        });
        $botman->hears('Buongiorno', function (BotMan $bot) {
            $bot->reply('Buongiorno a lei!');
        });



        $botman->hears('How are you(.*)', function ($bot) {
            $bot->reply('I\'m fine. Thanks!');
        });
        $botman->hears('Come stai(.*)', function ($bot) {
            $bot->reply('Bene, grazie!');
        });

        $botman->hears('Che tempo fa (.*) {location}', function ($bot, $location) {
            $apikey = Yii::$app->params['openweather_key'];

            // echo '<pre>'.print_r($apikey,true);exit;
            $url = 'https://api.openweathermap.org/data/2.5/weather?q='
                .urlencode($location)
                .'&appid='.$apikey
                .'&lang=it';

            $response = json_decode(file_get_contents($url));
            $array = $response->weather[0];

            // echo '<pre>'.print_r($response->weather[0],true);exit;
            $bot->reply('Il tempo a ' .$response->name. ' è:');
            $bot->reply($array->description);
            $bot->reply('La temperatura è di '. (round($response->main->temp /13,1)).' gradi.');

        });

        // start listening
        $botman->listen();
        // request must be terminated.
        die();
    }

}
