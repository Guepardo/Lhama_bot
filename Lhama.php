<?php 
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/util/NamesFactory.php';
date_default_timezone_set('America/Sao_Paulo');
session_start();

use Telegram\Bot\Api;

class Lhama{

	private $telegram   = null; 
	private $chat_id    = ''; 
	private $api_key    = ''; 
	private $lhama_key  = '6687ed3ce38df48acfc1b1a0efc900e8_exemple'; 

	public function __construct(){
		$this->telegram = new Api($this->api_key);
	}

	public function sendMessage($message = 'nothing, bip bip'){
		$toSend = ['chat_id' => $this->chat_id,
				   'text'    => $message, 
				   'parse_mode' => 'HTML' ]; 

		$this->telegram->sendMessage($toSend); 
	}

	private function verifyParams(){
		if(!isset($_POST))
			return false;

		if(!array_key_exists('action',$_POST) || !array_key_exists('lhama_key',$_POST) )
			return false; 

		return true; 
	}

	private function buildMessage($action){
		//list of lhama actions: 
		//click 1 
		//open_site 2

		$whatAction = ''; 

		switch($action){
			case '1': 
				$whatAction = "Clicks no site"; 
			break; 
			case '2': 
				$whatAction = "Abriu o site"; 
			break; 
			default:
				die(self::buildResponse(false)); 
		}

		//Get some informations from session: 
		self::checkSession(); 

		$toSend = ''; 

		if($_SESSION['count'] == 0){
			$toSend = "Humn, parece que alguém acabou de interagir com site e eu não o vi nas últimas 24 horas."; 
			$toSend .= "Vou chama-lo(a) de <b>". $_SESSION['name'].'</b>'; 
		}else{
			$toSend = "Já vi o/a <b>". $_SESSION['name'].'</b> aqui antes.';
			$toSend .= "Ele/Ela já interagiu <b>". $_SESSION['count']. "</b> vezes com o site.";
		}

		if(!($_SESSION['count'] % 5 == 0) )
			die;

		$ip =  $_SERVER["REMOTE_ADDR"]; 

		$toSend .= PHP_EOL;
		$toSend .='<code>'.PHP_EOL.'Outras informações:'.PHP_EOL.'</code>'; 
		$toSend .= PHP_EOL;
		$toSend .= '<b>Ip: </b>'. $ip.PHP_EOL; 
		$toSend .= '<b>Ação executada no site: </b> '. $whatAction. PHP_EOL; 
		$toSend .= '<b>Última vez visto às: </b>'. $_SESSION['last_request']; 

		$_SESSION['last_request'] = date('Y-m-d H:i:s'); 

		return $toSend; 
	}

	private function checkSession(){
		if( !isset($_SESSION['name']))
			$_SESSION['name'] = NamesFactory::getName();
		

		if( !isset($_SESSION['count']))
			$_SESSION['count'] = 0; 
		else
			$_SESSION['count'] += 1; 
	}

	private function buildResponse($status = true){
		return json_encode(['status' => $status]);
	}

	private function checkLhamaKey($lhamaKey){
		return ( $lhamaKey == $this->lhama_key);
	}

	public function handle(){

		if(!self::verifyParams())
			die(self::buildResponse(false));


		$lhamaKey = $_POST['lhama_key']; 
		$action   = $_POST['action'];   

		if(!self::checkLhamaKey($lhamaKey))
			die(self::buildResponse(false)); 

		$message = self::buildMessage($action); 

		self::sendMessage($message); 
		die(self::buildResponse());
	}
}

