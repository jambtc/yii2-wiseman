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
// use app\components\Middleware\ReceivedMiddleware;
// use app\components\Middleware\DialogFlowMiddleware;
// use BotMan\BotMan\Middleware\Dialogflow;
// use Botman\Botman\Middleware\DialogFlow\V2\DialogFlow;


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
        // Set your web driver
        DriverManager::loadDriver(\BotMan\Drivers\Telegram\TelegramDriver::class);
        DriverManager::loadDriver(\BotMan\Drivers\Web\WebDriver::class);
        // config web driver
        $config = [
            'web' => [
            	'matchingData' => [
                    'driver' => 'web',
                ],
            ],
            'telegram' => [
	            'token' => Yii::$app->params['telegram_wiseman_token'],
            ]
        ];
        // Create BotMan instance
        $botman = BotManFactory::create($config, new \idk\yii2\botman\Cache());

        // google dialog flow
        $dialogflow = \BotMan\Middleware\DialogFlow\V2\DialogFlow::create('it');
        $botman->middleware->received($dialogflow);

        // support dialog flow
        $botman->hears('support(.*)', function (Botman $bot) {
            $extras = $bot->getMessage()->getExtras();
            $apiReply = $extras['apiReply'];
            $bot->reply(Yii::$app->formatter->asDateTime(time()) . ' - ' .$apiReply);
        })->middleware($dialogflow);

        // smaltalk dialog flow
        $botman->hears('smalltalk(.*)', function (Botman $bot) {
            $extras = $bot->getMessage()->getExtras();
            $apiReply = $extras['apiReply'];
            // $apiAction = $extras['apiAction'];
            // $apiIntent = $extras['apiIntent'];
            // $apiContexts = $extras['apiContexts'];

            $bot->reply($apiReply);
        })->middleware($dialogflow);

        // weather dialog flow
        $botman->hears('weather(.*)', function (Botman $bot) {
            $extras = $bot->getMessage()->getExtras();
            $apiAction = $extras['apiAction'];
            $apiContexts = $extras['apiContexts'];

            $location = $apiContexts[0]['parameters']['address.original'] ?? 'napoli';
            $apikey = Yii::$app->params['openweather_key'];
            // // echo '<pre>'.print_r($value,true);exit;
            $url = 'https://api.openweathermap.org/data/2.5/weather?q='
                .urlencode($location)
                .'&appid='.$apikey
                .'&lang=it&units=metric';

            $response = json_decode(file_get_contents($url));
            $array = $response->weather[0];

            $bot->reply('Il tempo a ' .$response->name. ' è:');
            $bot->reply($array->description);
            $bot->reply('La temperatura è di '. $response->main->temp .' gradi.');
        })->middleware($dialogflow);

        // fallback
        $botman->fallback(function ($bot) {
            // $bot->reply($bot->getMessage()->getExtras('apiReply'));
            $message = $bot->getMessage();
            $value = $message->getText();

            if (trim($value) !== ''){
                // $timestamp = $bot->getMessage()->getExtras('timestamp');
                $bot->reply('Il tuo user_id è:' . $bot->getMessage()->getSender());
                $bot->reply(Yii::$app->formatter->asDateTime(time()) .' - Non ho capito. Prova a digitare "help"');
            }
        });

        // gif
        $botman->hears('gif {name}', function($bot, $name){
            $apikey = Yii::$app->params['giphy_developer_api'];
            $url = 'https://api.giphy.com/v1/gifs/search?api_key='.$apikey
                    .'&q='.urlencode($name)
                    .'&limit=1&offset=0&rating=g&lang=it';
            $response = json_decode(file_get_contents($url));
            $image = $response->data[0]->images->downsized_large->url;
            $message = OutgoingMessage::create('Questa è la tua gif')->withAttachment(
                new Image($image)
            );

            $bot->reply($message);
        });


        // help
        $botman->hears('help|aiuto|guida', function($bot) {
            $bot->reply('Questa è la tua guida. Segui queste istruzioni...');
            $bot->reply('...ehmmmm  prova a digitare "poa"');
        })->skipsConversation();

        // exit conversation
        $botman->hears('stop|ferma|quit|bye|esci|abbandona', function($bot) {
            $bot->reply('Ciao. A presto.');
        })->stopsConversation();


        // poa conversation class
        $botman->hears('poa(.*)', function ($bot) {
            $bot->reply('Ok. Va bene, cominciamo.');
            $bot->startConversation(new PoaConversation);
        });


        // start listening
        $botman->listen();
        // request must be terminated.
        die();
    }

}
