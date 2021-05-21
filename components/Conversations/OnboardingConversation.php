<?php

namespace app\components\Conversations;

use BotMan\BotMan\Messages\Conversations\Conversation;

class OnboardingConversation extends Conversation
{
    protected $name;
    protected $age;
    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {

        $this->ask('Come ti chiami?', function ($answer) {
            $value = $answer->getText();

            if (!$this->validateAnswer($value))
                return $this->repeat();


            $this->name = $value;
            $this->askAge();
        });
    }

    protected function validateAnswer($value)
    {
        if (trim($value) === ''){
            $this->say('Correggi!');
            return false;
        }
        return true;

    }

    protected function askAge()
    {
        $this->ask('Quanti anni hai?', function ($answer){
            $value = $answer->getText();
            if (!$this->validateAnswer($value))
                return $this->repeat();

            $this->age = $value;

            $this->say('Piacere, '.$this->name);
            $this->say('Ok. Hai '.$this->age .' anni.');

            $this->askAvatar();
        });
    }

    protected function askAvatar()
    {
        $this->askForImages('Carica una tua fotografia.', function ($images) {
            $this->say('Ok. Ho ricevuto '.count($images). ' immagini.');
        }, function() {
            $this->say('Uhmmm, questa non sembra un\'immagine.');
            $this->say('Per finire digita "stop"');
            $this->repeat();
        });
    }
}
