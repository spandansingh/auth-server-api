<?php
namespace Auth;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ApiClient{
	
	public $config = array();
	public $user;

	public $compulsory_keys = array(
		'Auth Key' => 'KEY',
		'Auth Secret' => 'SECRET',
		'App Login Uri' => 'LOGIN_URI'
	);

	public $optional_keys = array(
		'AUTH_CHECK_URL',
		'AUTH_LOGOUT_URL',
		'COOKIE_EXPIRE',
		'AUTH_TOKEN_NAME',
	);

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
	}

	function checkForSessionStatus(){
		if(session_status() != PHP_SESSION_ACTIVE){
			trigger_error('Please start the session first');
			exit();
		}
	}

	function isLoggedIn($callback_url = NULL, $login_url = NULL, $token = NULL){

		$this->checkForSessionStatus();

		if(empty($login_url)){
			$login_url = $this->config['ROOT'] . $this->config['LOGIN_URI'];
		}

		if(empty($token)){
			$token = isset($_SESSION[$this->config['AUTH_TOKEN_NAME']])?$_SESSION[$this->config['AUTH_TOKEN_NAME']]:NULL;
		}
		

		if(empty($callback_url)){
			$callback_url = $this->getHttpHost() . $_SERVER['REQUEST_URI'];
		}

		$params = [
		   "key" => $this->config['KEY'],
		   "secret" => $this->config['SECRET'],
		   "token" => $token,
		   "callback_url" => $callback_url,
		   "login_url" => $login_url
		];

		$url = $this->config['AUTH_CHECK_URL'];
		$client = new Client();

		try {
		    $response = $client->post($url, ['json'=>$params]);
		    return $response->json()['data'];
		} catch (RequestException $e) {
			$response = $e->getResponse();

			if(isset($response->json()['login_url'])){
				$login_url = $response->json()['login_url'];
			}else{
				print_r($response->json()['message']);
				exit();
			}

			header('Location:' . $login_url);
			exit();
		}	 
	}

	function doLogin($token = NULL,$callback_uri = NULL){

		if(empty($token)){
			$token = isset($_GET['token'])?$_GET['token']:NULL;
		}
		
		if(empty($callback_uri)){
			$callback_uri = isset($_GET['callback_url'])?$_GET['callback_url']:NULL;	
		}

		if(empty($token)){
			exit('Empty Auth Token');
		}
		
		if(empty($callback_uri)){
			$callback_uri = $this->getHttpHost();
		}

		$this->checkForSessionStatus();

		$_SESSION[$this->config['AUTH_TOKEN_NAME']] = $token;
		header('Location:' . $callback_uri);
		exit();
	}

	function doLogout(){
		$this->checkForSessionStatus();
		unset($_SESSION[$this->config['AUTH_TOKEN_NAME']]);
		header('Location:' . $this->config['AUTH_LOGOUT_URL']);
		exit();
	}

	function getHttpHost(){
		return (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
	}
}