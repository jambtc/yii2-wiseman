<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;

use Botman\BotMan\Messages\Attachments\Audio;
use Botman\BotMan\Messages\Attachments\Video;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;

// middleware
use app\components\Middleware\ReceivedMiddleware;


// my conversations
use app\components\Conversations\PoaConversation;
use app\components\Conversations\OnboardingConversation;
use app\components\Conversations\ButtonConversation;

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

        // received: anche in caso di fallback vengono impostati i parametri Extras
        $botman->middleware->received(new ReceivedMiddleware());

        $botman->fallback(function ($bot) {
            $message = $bot->getMessage();
            $value = $message->getText();

            if (trim($value) !== ''){
                $timestamp = $bot->getMessage()->getExtras('timestamp');
                $bot->reply($timestamp .' : Non capisco');
                $bot->reply('Prova a digitare "help"');
            }
        });

        // middleware class
        $botman->hears('timestamp', function ($bot) {
            $timestamp = $bot->getMessage()->getExtras('timestamp');
            $bot->reply( $timestamp . ' : ' .$bot->getMessage()->getText() );
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
                .'&lang=it&units=metric';

            $response = json_decode(file_get_contents($url));



                $array = $response->weather[0];

                // echo '<pre>'.print_r($response->weather[0],true);exit;
                $bot->reply('Il tempo a ' .$response->name. ' è:');
                $bot->reply($array->description);
                $bot->reply('La temperatura è di '. $response->main->temp .' gradi.');


        });

        $botman->hears('/gif {name}', function($bot, $name){
            $apikey = Yii::$app->params['giphy_developer_api'];
            $url = 'https://api.giphy.com/v1/gifs/search?api_key='.$apikey
                    .'&q='.urlencode($name)
                    .'&limit=1&offset=0&rating=g&lang=it';
            $response = json_decode(file_get_contents($url));

            // $data =  $response->data[0];
            $image = $response->data[0]->images->downsized_large->url;

            $message = OutgoingMessage::create('Questa è la tua gif')->withAttachment(
                new Image($image)
            );

            $bot->reply($message);
        });


        $botman->hears('help|aiuto|guida', function($bot) {
            $guida = [
                'poa',
                'come ti chiami',
                'Hi',
                'hello',
                'ciao',
                'buongiorno',
                'how are you',
                'come stai',
                'che tempo fa a {location}',
                '/gif {nome}',
                // '/video',
                // 'il mio nome è {nome}',
                // 'dimmi il mio nome',
                // 'mi chiamo {nome}',
                'iniziamo',
                'scegli'

            ];
            $bot->reply('Questa è la tua guida. Segui queste istruzioni...');
            $bot->reply('<pre class="text-light">'.print_r($guida,true).'</pre>');
        })->skipsConversation();

        $botman->hears('stop|ferma', function($bot) {
            $bot->reply('Ciao. A presto.');
        })->stopsConversation();

        // conversation class
        $botman->hears('iniziamo(.*)', function ($bot) {
            $bot->reply('Va bene, cominciamo.');
            $bot->startConversation(new OnboardingConversation);

        });

        // conversation class
        $botman->hears('poa(.*)', function ($bot) {
            $bot->reply('Ok. Va bene, cominciamo.');
            $bot->startConversation(new PoaConversation);

        });

        // conversation class
        $botman->hears('scegli(.*)', function ($bot) {
            $bot->startConversation(new ButtonConversation);

        });


        // start listening
        $botman->listen();
        // request must be terminated.
        die();
    }

}
