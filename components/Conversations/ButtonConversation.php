<?php

namespace app\components\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;


class ButtonConversation extends Conversation
{
    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $question = Question::create("Huh - you woke me up. What do you need?")
            //->fallback('Unable to ask question')
            //->callbackId('ask_reason')
            ->addButtons([
                Button::create('Gatti')->value('cats'),
                Button::create('Cani')->value('dogs'),
                Button::create('Tell a joke')->value('joke'),
                Button::create('Give me a fancy quote')->value('quote'),
            ]);

        $this->ask($question, function ($answer) {
            if ($answer->isInteractiveMessageReply()) {

                // echo '<pre>'.print_r($answer);exit;

                $choice = $answer->getValue();
                $this->say('Hai scelto: '.$answer->getValue());

                if ($choice === 'joke') {
                    $joke = json_decode(file_get_contents('http://api.icndb.com/jokes/random'));
                    $this->say($joke->value->joke);
                } elseif (($choice === 'cats') || ($choice === 'dogs')) {
                    $giphy_developer_api = 'wOglJGSoV1WXMtYL8oij79bQzeIjUhFR';
                    $url = 'https://api.giphy.com/v1/gifs/search?api_key='.$giphy_developer_api
                            .'&q='.urlencode($choice)
                            .'&limit=1&offset=0&rating=g&lang=it';
                    $response = json_decode(file_get_contents($url));
                    $image = $response->data[0]->images->downsized_large->url;
                    $message = OutgoingMessage::create('Questa è la tua gif')->withAttachment(
                        new Image($image)
                    );

                    $this->say($message);
                } else {
                    $this->say(Inspiring::quote());
                }
            } else {
                $this->repeat();
            }
        });
    }
}
