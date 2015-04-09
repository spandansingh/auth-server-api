<?php
namespace AuthServer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ApiClient{
	
	public $config = array();
	private $user;

	private $compulsory_keys = array(
		'Auth Key' => 'KEY',
		'Auth Secret' => 'SECRET',
		'App Login Uri' => 'LOGIN_URI'
	);

	private $optional_keys = array(
		'AUTH_HOME_URL',
		'AUTH_CHECK_URL',
		'AUTH_LOGOUT_URL'
	);

	private $token;

	private $callback_uri;

	private $client;

	function __construct($config = array()){
		
		$config_file = dirname(__FILE__) . "/" . "config.php";

		if(!empty($config)){
			
			foreach($this->compulsory_keys as $msg=>$key){
				if(!isset($config[$key])){
					trigger_error('Please Provide the ' . $msg);
				}else{
					$this->config[$key] = $config[$key];
				}
			}

			foreach($this->optional_keys as $key){
				
				if(!isset($config[$key])){ 
					
					if(!isset($pre_config)){
						if(!is_file($config_file)){
							exit('Configuration File of Auth Server Missing on ' . $config_file);	
						}
						$pre_config = include $config_file;
					}
					
					$this->config[$key] = $pre_config[$key];
				}else{
					$this->config[$key] = $config[$key];
				}
			}

			if(!isset($config['ROOT'])){
				$this->config['ROOT'] = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'];
			}else{
				$this->config['ROOT'] = $config['ROOT'];
			}

		}else{
			trigger_error('Please Provide Configurations of Auth Server');
		}

		$this->client = $this->getGuzzlePHPClient();
	}

	public function getParams(){
		return [
			"key" => $this->config['KEY'],
			"secret" => $this->config['SECRET']
		];
	}

	public function getGuzzlePHPClient(){
		
		$this->client = new Client([
		    'base_url' => $this->config['AUTH_HOME_URL'],
		    'defaults' => [
		 	    'headers' => [
		 	    	'Accept' => 'application/json'
		 	    ],
		        'query'   => [
			        'key' => $this->config['KEY'],
			        'secret' =>  $this->config['SECRET']
			    ]
			]
		]);

		return $this->client;
	}

	function isLoggedIn($token = NULL, $callback_url = NULL, $login_url = NULL){
		
		if(empty($login_url)){
			$login_url = $this->config['ROOT'] . $this->config['LOGIN_URI'];
		}
		

		if(empty($callback_url)){
			$callback_url = $this->getHttpHost() . $_SERVER['REQUEST_URI'];
		}

    	$params["token"] = $token;
	    $params["callback_url"] = $callback_url;
	    $params["login_url"] = $login_url;
	    
		try {
		    $response = $this->client->post('/check', ['json'=>$params]);
		    $this->user = (object) $response->json()['data'];
		    return $response->json();
		} catch (RequestException $e) {
			$response = $e->getResponse();
			if(isset($response) && isset($response->json()['login_url'])){
				$login_url = $response->json()['login_url'];
			}else{
				header('Location:' . $this->config['AUTH_HOME_URL'] . '?flash_msg=' . $response->json()['message']);
				exit();
			}

			header('Location:' . $login_url);
			exit();
		}	 
	}

	function doLogin($token = NULL,$callback_uri = NULL){

		$this->token = $token;
		$this->callback_uri = $callback_uri;

		if(empty($this->token)){
			$this->token = isset($_GET['token'])?$_GET['token']:NULL;
		}
		
		if(empty($this->callback_uri)){
			$this->callback_uri = isset($_GET['callback_url'])?$_GET['callback_url']:NULL;	
		}

		if(empty($this->token)){
			return array(
				'msg' => 'Empty Auth Token',
				'status' => false
			);
		}
		
		if(empty($this->callback_uri)){
			$this->callback_uri = $this->getHttpHost();
		}
		
		return array('status'=>true);
	}

	public function getToken(){
		return $this->token;
	}

	public function getCallbackUri(){
		return $this->callback_uri;
	}

	public function doLogout(){
		$url = $this->makeUrl($this->config['AUTH_LOGOUT_URL']);
		header('Location:' . $url);
		exit();
	}

	public function getHttpHost(){
		return (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
	}

	public function user($key = NULL){

		if(!empty($key)){
			return $this->user->{$key};
		}

		return $this->user;
	}

	public function getStudents($query = NULL){	

		try {
			if(!empty($query)){
				$params["query"] = json_encode($query);
				$response = $this->client->post('/students', ['json'=>$params]);
			}else{
				  $response = $this->client->get('/students/all');		
			}
		  
		    return $response->json()['data'];
		} catch (RequestException $e) {
			$response = $e->getResponse();
			$message = isset($response->json()['message']) ? $response->json()['message']:$response->json()['error']['message'];
			trigger_error($message);
			exit();
		}	 
	}

	public function makeUrl($uri){
		return $this->config['AUTH_HOME_URL'] . $uri;
	}

	public function getFaculties($query = NULL){
		
		try {
			if(!empty($query)){
				$params["query"] = json_encode($query);
				$response = $this->client->post('/faculties', ['json'=>$params]);
			}else{
				  $response = $this->client->get('/faculties/all');		
			}

		    return $response->json()['data'];
		} catch (RequestException $e) {
			$response = $e->getResponse();
			$message = isset($response->json()['message']) ? $response->json()['message']:$response->json()['error']['message'];
			trigger_error($message);
			exit();
		}	 
	}
}