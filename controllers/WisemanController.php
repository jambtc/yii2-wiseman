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


        // google dialog flow
        // $dialogflow = Dialogflow::create(Yii::$app->params['google_dialog_flow'])->listenForAction();
        // $dialogflow = DialogFlow::create('it');
        $dialogflow = \BotMan\Middleware\DialogFlow\V2\DialogFlow::create('it');

        $botman->middleware->received($dialogflow);

        $botman->hears('support(.*)', function (Botman $bot) {
            $extras = $bot->getMessage()->getExtras();
            $apiReply = $extras['apiReply'];
            $bot->reply($apiReply);
        })->middleware($dialogflow);


        $botman->hears('smalltalk(.*)', function (Botman $bot) {
            $extras = $bot->getMessage()->getExtras();

            $apiReply = $extras['apiReply'];
            // $apiAction = $extras['apiAction'];
            // $apiIntent = $extras['apiIntent'];
            // $apiContexts = $extras['apiContexts'];

            $bot->reply($apiReply);
            // $bot->reply('action: '.$apiAction);
            // $bot->reply('intent: '.$apiIntent);
            // $bot->reply('context: '.print_r($apiContexts));
        })->middleware($dialogflow);

        $botman->hears('weather(.*)', function (Botman $bot) {
            $extras = $bot->getMessage()->getExtras();
            // $param = $bot->getParameters();
            //
            // echo '<pre>'.print_r($param,true);exit;
            // $apiReply = $extras['apiReply'];
            $apiAction = $extras['apiAction'];
            // $apiIntent = $extras['apiIntent'];
            // $apiActionIncomplete = $extras['apiActionIncomplete'];
            // $apiParameters = $extras['apiParameters'];
            $apiContexts = $extras['apiContexts'];

            $location = $apiContexts[0]['parameters']['address.original'] ?? 'napoli';

            // $bot->reply('Ecco la risposta da google api');
            // $bot->typesAndWaits(1);

            // $bot->reply('reply: '.$apiReply);
            // $bot->reply('location: '.$location);
            // $bot->reply('action: '.$apiAction);
            // $bot->reply('intent: '.$apiIntent);
            // $bot->reply('action incomplete: '.$apiActionIncomplete);
            // $bot->reply('parameters: '.print_r($apiParameters));
            // $bot->reply('context: '.print_r($apiConteexts));

            $apikey = Yii::$app->params['openweather_key'];
            // $value = json_decode($reply);
            // $location = $value['outputContexts']['parameters']['city'] ?? 'Napoli';
            //
            //
            //
            // // echo '<pre>'.print_r($value,true);exit;
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
        })->middleware($dialogflow);

        $botman->fallback(function ($bot) {
            // $bot->reply($bot->getMessage()->getExtras('apiReply'));
            $message = $bot->getMessage();
            $value = $message->getText();

            if (trim($value) !== ''){
                $timestamp = $bot->getMessage()->getExtras('timestamp');
                $bot->reply($timestamp .' : Non capisco. Prova a digitare "help"');
            }
        });



        $botman->hears('come ti chiami(.*)', function (BotMan $bot) {
            $bot->reply('Il mio nome è Wiseman, che significa "Uomo saggio"');
        });


        $botman->hears('gif {name}', function($bot, $name){
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
                // 'come ti chiami',
                // 'Hi',
                // 'hello',
                // 'ciao',
                // 'buongiorno',
                // 'how are you',
                // 'come stai',
                'che tempo fa a {location}',
                // '/gif {nome}',
                // '/video',
                // 'il mio nome è {nome}',
                // 'dimmi il mio nome',
                // 'mi chiamo {nome}',
                // 'iniziamo',
                // 'scegli'

            ];
            $bot->reply('Questa è la tua guida. Segui queste istruzioni...');
            $bot->reply('<pre class="text-light">'.print_r($guida,true).'</pre>');
        })->skipsConversation();

        $botman->hears('stop|ferma|ciao|bye', function($bot) {
            $bot->reply('Ciao. A presto.');
        })->stopsConversation();



        // conversation class
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
