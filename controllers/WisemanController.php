<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

use app\components\ApiLog;
use yii\httpclient\Client;

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
	public $enableCsrfValidation = false;

    public function beforeAction($action)
	{
    	$this->enableCsrfValidation = false;
    	return parent::beforeAction($action);
	}

    // scrive a video
    private function log($text, $die=false){
        // $log = new ApiLog;
        // $time = "\r\n" .date('Y/m/d h:i:s a - ', time());
        // // echo  $time.$text;
        // $log->save('wiseman','wiseman','index', $time.$text, $die);
    }

    public function actionIndex()
    {
        $this->log("Start Wiseman log");

        $request = Yii::$app->request;
        $post = $request->post();
        $rawcontent = file_get_contents('php://input');

        $this->log('$_POST stream is: <pre>
            '.print_r($post,true).'
        </pre>');

        $this->log('rawcontent stream is: <pre>
            '.print_r($rawcontent,true).'
        </pre>');

        // Set right web driver
        if (isset($_POST) && isset($_POST['driver']) && $_POST['driver'] == 'web' ){
            DriverManager::loadDriver(\BotMan\Drivers\Web\WebDriver::class);
            $config = [
                'web' => [
                    'matchingData' => [
                        'driver' => 'web',
                    ],
                ],
            ];
        } else {
            DriverManager::loadDriver(\BotMan\Drivers\Telegram\TelegramDriver::class);
            $config = [
                'telegram' => [
                    'token' => Yii::$app->params['telegram_wiseman_token'],
                ]
            ];
        }
        // Create BotMan instance
        $botman = BotManFactory::create($config, new \idk\yii2\botman\Cache());

        // google dialog flow
        $dialogflow = \BotMan\Middleware\DialogFlow\V2\DialogFlow::create('it');
        $botman->middleware->received($dialogflow);

        // lavoroagile faq dialog flow
        $botman->hears('lavoroagile(.*)', function (Botman $bot) {
            $extras = $bot->getMessage()->getExtras();
            $apiReply = $extras['apiReply'];
            $bot->reply('[AdE]: ' .$apiReply);
        })->middleware($dialogflow);

        // support dialog flow
        $botman->hears('support(.*)', function (Botman $bot) {
            $this->log('$bot stream is: <pre>
                '.print_r($bot->getMessage(),true).'
            </pre>');
            $extras = $bot->getMessage()->getExtras();
            $apiReply = $extras['apiReply'];
            $bot->reply(Yii::$app->formatter->asDateTime(time()) . ' - [Supporto tecnico]: ' .$apiReply);
        })->middleware($dialogflow);

        // smaltalk dialog flow
        $botman->hears('smalltalk(.*)', function (Botman $bot) {
            $this->log('$bot stream is: <pre>
                '.print_r($bot->getMessage(),true).'
            </pre>');
            $extras = $bot->getMessage()->getExtras();
            $apiReply = $extras['apiReply'];
            // $apiAction = $extras['apiAction'];
            // $apiIntent = $extras['apiIntent'];
            // $apiContexts = $extras['apiContexts'];

            $bot->reply($apiReply);
        })->middleware($dialogflow);

        // fallback dialog flow
        $botman->hears('fallback.google(.*)', function (Botman $bot) {
            $this->log('$bot stream is: <pre>
                '.print_r($bot->getMessage(),true).'
            </pre>');
            $extras = $bot->getMessage()->getExtras();
            $apiReply = $extras['apiReply'];
            $bot->reply($apiReply);
        })->middleware($dialogflow);

        // weather dialog flow
        $botman->hears('weather(.*)', function (Botman $bot) {
            $this->log('$bot stream is: <pre>
                '.print_r($bot->getMessage(),true).'
            </pre>');
            $extras = $bot->getMessage()->getExtras();
            $apiAction = $extras['apiAction'];
            $apiContexts = $extras['apiContexts'];

            $location = $apiContexts[0]['parameters']['address.original'] ?? 'napoli';
            $apikey = Yii::$app->params['openweather_key'];
            $url = 'https://api.openweathermap.org/data/2.5/weather?q='
                .urlencode($location)
                .'&appid='.$apikey
                .'&lang=it&units=metric';

            $client = new Client;
            $request = $client->createRequest()
                ->setMethod('GET')
                ->setUrl($url)
                ->setOptions([
                    'timeout' => 5, // set timeout to 1 seconds for the case server is not responding
                ])
                ->send();

            if ($request->getisOk()){
                $response = $request->getData();
                // echo '<pre>'.print_r($response,true);exit;

                $bot->reply('Il tempo a ' .$response['name']. ' ??:');
                $bot->reply($response['weather'][0]['description']);
                $bot->reply('La temperatura ?? di '. $response['main']['temp'] .' gradi.');
                $bot->reply('L`umidit?? ?? al '. $response['main']['humidity'] .'%.');
            } else {
                $bot->reply('Non sono riuscito a capire la localit??. Puoi ripetere?');
            }
        })->middleware($dialogflow);

        /**
        * These are not google intents
        * But it's better to create a google intent `action` with no response
        */

        // telegram start
        $botman->hears('/start', function (Botman $bot) {
            $bot->reply('??? Ciao! Mi chiamo Wiseman e sono il tuo assistente.');
            $bot->reply('Possiamo chiacchierare liberamente, parlare del tempo o posso fornirti supporto tecnico.');
        });

        // gif
        $botman->hears('/gif {name}', function(Botman $bot, $name){
            $this->log('$bot stream is: <pre>
            '.print_r($bot->getMessage(),true).'
            </pre>');

            $apikey = Yii::$app->params['giphy_developer_api'];
            $url = 'https://api.giphy.com/v1/gifs/search?api_key='.$apikey
            .'&q='.urlencode($name)
            .'&limit=1&offset=0&rating=g&lang=it';
            $client = new Client;
            $request = $client->createRequest()
            ->setMethod('GET')
            ->setUrl($url)
            ->setOptions([
                'timeout' => 5, // set timeout to 1 seconds for the case server is not responding
            ])
            ->send();

            if ($request->getisOk()){
                $response = $request->getData();
                $image = $response['data'][0]['images']['downsized_large']['url'];
                $message = OutgoingMessage::create('Questa ?? la tua gif')->withAttachment(
                    new Image($image)
                );
                $bot->reply($message);
            } else {
                $bot->reply('Non sono riuscito a comprendere la gif. Puoi ripetere?');
            }
        });

        // poa conversation class
        $botman->hears('/poa(.*)', function (Botman $bot) {
            $bot->reply('Puoi verificare la quantit?? di token presenti su un indirizzo.');
            $bot->startConversation(new PoaConversation);
        });

        // fallback
        $botman->fallback(function (Botman $bot) {
            $this->log('$bot stream is: <pre>
                '.print_r($bot->getMessage(),true).'
            </pre>');
            // $bot->reply($bot->getMessage()->getExtras('apiReply'));
            $message = $bot->getMessage();
            $value = $message->getText();

            if (trim($value) !== ''){
                // $timestamp = $bot->getMessage()->getExtras('timestamp');
                // $bot->reply('Il tuo user_id ??:' . $bot->getMessage()->getSender());
                $bot->reply('Non ho capito. Prova a digitare "help"');
            }
        });



        // help
        $botman->hears('help|aiuto|guida', function(Botman $bot) {
            $this->log('$bot stream is: <pre>
                '.print_r($bot->getMessage(),true).'
            </pre>');
            $bot->reply('Digitando /start avrai una lista di comandi da eseguire.');
            $rnd = rand(1,10);
            if ($rnd > 7){
                $bot->reply('...ehmmmm  prova a digitare "poa".');
            }

        })->skipsConversation();

        // exit conversation
        $botman->hears('stop|ferma|quit|bye|esci|abbandona', function(Botman $bot) {
            $this->log('$bot stream is: <pre>
                '.print_r($bot->getMessage(),true).'
            </pre>');
            $bot->reply('Ciao. A presto.');
        })->stopsConversation();



        // start listening
        $botman->listen();
        // request must be terminated.
        die();
    }

}
