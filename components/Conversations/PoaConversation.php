<?php

namespace app\components\Conversations;

use app\components\Erc20;
use app\components\Settings;
use BotMan\BotMan\Messages\Conversations\Conversation;
use Web3\Web3;
use Web3\Contract;


class PoaConversation extends Conversation
{
    const NODE = 'https://poa.fid3lize.tk:443';

    protected $address;

    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {

        $this->ask('Inserisci l`indirizzo del token', function ($answer) {
            $this->address = $answer->getText();

            if (!$this->isValidAddress($this->address)){
                $this->say('Uhmmm, questo non sembra un indirizzo valido.');
                $this->say('Per interrompere digita "stop"');
                return $this->repeat();
            }

            $this->say('Indirizzo valido');


            // $this->say('Il bilancio è: '. $this->balanceToken());
            $erc20 = new Erc20;
            $this->say('Il bilancio è di: <b>'. $erc20->balance($this->address) .'</b> token.');
            $this->say('Il gas è di: <b>'. $this->balanceGas() .'</b>.');


        });
    }

    /**
	 * @param POST string address the Ethereum Address to be paid
	 */
	protected function isValidAddress($address)
    {
		$web3 = new Web3(self::NODE);
        $utils = $web3->utils;
		$response = $utils->isAddress($address);

        if (!($response))
            return false;
        else
            return true;

	}

    protected function balanceGas()
    {
        //recupero il balance
        $balance = 0;
        $web3 = new Web3(self::NODE);
        $web3->eth->getBalance($this->address, function ($err, $response) use (&$balance){
            $jsonBody = json_decode($err);

            if ($jsonBody !== NULL){
                throw new HttpException(404,$jsonBody['error']['message']);
            }
            $balance = $response->toString() ;
        });
        $value = (string) $balance * 1;
		$balance = round ($value / (1*pow(10,18)), 4); //1000000000000000000;
        return $balance;
    }







}
