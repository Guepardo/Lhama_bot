<?php 
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/util/NamesFactory.php';

date_default_timezone_set('BRT');

use Telegram\Bot\Api;

class Lhama{

	private $telegram   = null; 
	private $chat_id    = ''; 
	private $api_key    = ''; 
	private $lhama_key  = 'nadas'; 

	public function __construct(){
		$this->telegram = new Api($this->api_key);
	}

	private function sendMessage($message = 'nothing, bip bip'){
		$toSend = ['chat_id' => $this->chat_id, 'text' => $message ]; 
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
		//click
		//open_site 

		$whatAction = ''; 

		switch($action){
			case 'click': 
				$whatAction = "Clicou no site"; 
			break; 

			case 'open_site': 
				$whatAction = "Abriu o site"; 

		}

		//Get some informations from session: 
		self::checkSession(); 

		$toSend = ''; 

		if($_SESSION['count'] == 0){
			$toSend = "Humn, parece que alguém acabou de interagir com site e eu não o vi nas últimas 24 horas."; 
			$toSend .= "Vou chama-lo(a) de ". $_SESSION['name']; 
		}else{
			$toSend = "Já o/a ". $_SESSION['name'].' aqui antes.';
			$toSend .= "Ele/Ela já interagiu ". $_SESSION['count']. " vezes com o site.";
		}

		$brower = $_SERVER['HTTP_USER_AGENT']; 

		$toSend .='\n Outra tipos de informações: \n'; 
		$toSend .= 'Navegador : '. $brower. '\n'; 
		$toSend .= 'Ação executada no site '. $whatAction. '\n'; 
		$toSend .= 'Última vez visto às '. $_SESSION['last_request']; 

		$_SESSION['last_request'] = date('H:m:s'); 
		return $toSend; 
	}

	private function checkSession(){
		if( empty($_SESSION['name']))
			$_SESSION['name'] = NamesFactory::getName();
		

		if( empty($_SESSION['count']))
			$_SESSION['count'] = 0; 
		else
			$_SESSION['count'] += 1; 
	
		$_SESSION['last_request'] = date('Y-m-d H:i:s'); 
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