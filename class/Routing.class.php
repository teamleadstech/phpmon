<?php

class Routing{

	protected $pages;
	protected $redirect;
	
	public function __construct(){
		$this->pages = array(
			'home' => array(
				'file' => 'page/home.phtml',
				'auth' => false,
			),
			'monitor' => array(
				'file' => 'page/monitor.phtml',
				'auth' => false,
			),
			'api' => array(
				'file' => 'page/api.phtml',
				'auth' => false,
			),
			'404' => array(
				'file' => 'page/404.phtml',
				'auth' => false,
			),
			'login' => array(
				'file' => 'page/login.phtml',
				'auth' => false,
			),
		);
		
		$this->redirect = array(
			'our-team'=>'team',
			'our-proof'=>'proof',
		);
	}
	
	public function parseRoute($request_uri){
		$request_uri = preg_replace('/\/$/','',$request_uri);
		if(strpos($request_uri, "?") !== false){
			$request_uri = substr($request_uri, 0, strpos($request_uri, "?"));
		}
		$params = @split("/", $request_uri);
		if(isset($params[1]) && !empty($params[1]) && isset($this->pages[$params[1]]) && !empty($this->pages[$params[1]])){
			$response = $this->loadPage($params[1]);
			return $response;
		}else if (isset($params[1]) && !empty($params[1]) && isset($this->redirect[$params[1]]) && !empty($this->redirect[$params[1]])){
			$response = $this->redirectPage($params[1]);
		}else{
			if(empty($params[1])){				
				$response = $this->loadPage('home');
			}else{
				$response = $this->loadPage('404');
			}
			return $response;
		}
	}
	
	protected function loadPage($page){
		return $this->getPageOutput(WWW_DIR.$this->pages[$page]['file']);
	}
	
	protected function redirectPage($page){
		$new_url = '//'.APP_DOMAIN.'/'.$this->redirect[$page];
		header("HTTP/1.1 301 Moved Permanently"); 
		header("Location: $new_url"); 
	}
	
	protected function getPageOutput($path){
		ob_start();
		include_once $path;
		$result = ob_get_clean();		
		return $result;
	}


}
