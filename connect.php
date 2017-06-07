<?php

	/**
	* 
	*/
	class Peruse {

		private static $baseUrl = 'https://app.goperuse.com/'; 

		public $clientUrl;

		private $user_token;

		private $auth_link;

		private $peruse_user;

		private $product;

		private $order;

		public function __construct() {

				$this->request_uri = strtok($_SERVER['REQUEST_URI'],'?');

		    $this->clientUrl = "http://" . $_SERVER['HTTP_HOST'] . $this->request_uri;
		}

		private function post($route, $body) {

			if(isset($_SESSION['peruse_access_token'])){

				$options['body'] = json_encode($body);
				$options['headers']['content-type'] = 'application/json';

				$request = $this->provider->getAuthenticatedRequest(
			      'POST',
			      $this::$baseUrl . 'api/graph/v1/' . $route,
			      $_SESSION['peruse_access_token'],
			      $options
			    );

				$response = $this->provider->getHttpClient()->send($request);
				return json_decode($response->getBody());
			}

			return null;

		}


		private function get($route) {

			if(isset($_SESSION['peruse_access_token'])){
				$request = $this->provider->getAuthenticatedRequest(
			      'GET',
			      $this::$baseUrl . 'api/graph/v1/' . $route,
			      $_SESSION['peruse_access_token']
			    );

				$response = $this->provider->getHttpClient()->send($request);
				return json_decode($response->getBody());
			}

			return null;

		}

		
		public function connect() {

			require_once 'vendor/autoload.php';

			session_start();

			define('CLIENT_ID', 'CJ1PJCVEQ000VBNDLECXWNHK2');
			define('CLIENT_SECRET', 'jvcwx7fhrj48jajg030j1q11vxw13lvwjehps6c87x');
			define('REDIRECT_URI', $this->clientUrl);

			if (CLIENT_ID && CLIENT_SECRET){
			  
			  $this->provider = new \League\OAuth2\Client\Provider\GenericProvider([
		    	'clientId'          =>  CLIENT_ID,
		    	'clientSecret'      =>  CLIENT_SECRET,
		    	'redirectUri'       => REDIRECT_URI,
			    'urlAuthorize'            => $this::$baseUrl . 'api/oauth2/authorize',
			    'urlAccessToken'          => $this::$baseUrl . 'api/oauth2/exchange',
			    'urlResourceOwnerDetails' => $this::$baseUrl . 'api/graph/v1'
			  ]);

			  if (isset($_REQUEST['logout'])) {
			  	session_unset();
				}

				if (isset($_SESSION['peruse_access_token'])) {

					//refresh token if necessary
					if($_SESSION['peruse_token_exp'] < time()){

						unset($_SESSION['peruse_access_token']);

			    	$access_token = $this->provider->getAccessToken('refresh_token', [
			        'refresh_token' => $_SESSION['peruse_refresh_token']
			    	]);	

			    	$_SESSION['peruse_access_token'] = $access_token->getToken();
						$_SESSION['peruse_refresh_token'] = $access_token->getRefreshToken();
						$_SESSION['peruse_token_exp'] = $access_token->getExpires();
					}

					$this->user_token = $_SESSION['peruse_access_token'];

					$this->peruse_user = $this->get('user');
					return;

				}


				if (!isset($_GET['code'])) {

			    $options = [
					   'scope' => ['email']
					];

			    $this->auth_link = $this->provider->getAuthorizationUrl($options);

			    // Get the state generated for you and store it to the session.
			    $_SESSION['oauth2state'] = $this->provider->getState();


				// Check given state against previously stored one to mitigate CSRF attack
				} elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {

				    if (isset($_SESSION['oauth2state'])) {
				        unset($_SESSION['oauth2state']);
				    }
				    
				    exit('Invalid state');

				} else {

				  try {

				  	$access_token = $this->provider->getAccessToken('authorization_code', [
				       'code' => $_GET['code']
				    ]);

						$_SESSION['peruse_access_token'] = $access_token->getToken();
						$_SESSION['peruse_refresh_token'] = $access_token->getRefreshToken();
						$_SESSION['peruse_token_exp'] = $access_token->getExpires();

				  	$redirect = $this->clientUrl; 

				  	header('Location:' . filter_var($redirect, FILTER_SANITIZE_URL));
				  	die();

				  } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

			      // Failed to get the access token or user details.
			      exit($e->getMessage());

				  }
				}

			}

		}

		public function clientUrl() { 

  		return $this->clientUrl;

  	}

		public function user_token() { 

  		return $this->user_token;

  	}

		public function auth_link() { 

  		return $this->auth_link;

  	}

  	public function post_product($body) { 

  		return $this->post('product', $body);

  	}

  	public function user() {

	  	return $this->peruse_user;

	  }

	  public function product($product_id, $product_type) {

	  	if(!$this->product){

	  		$this->product = $this->get('product/' . $product_type . '/' . $product_id);

	  	}

	  	return $this->product;

	  }


	  public function order() {

	  	if(!$this->order){

	  		$this->order = $this->get('order');

	  	}

	  	return $this->order;

	  }

	}


	$peruse = new Peruse;

	$peruse->connect();

	function current_url() { 
		global $peruse;

		return $peruse->clientUrl();
	}

	function peruse_user_token() { 
		global $peruse;

		return $peruse->user_token();
	}

	function peruse_login_link() {
		global $peruse;

		return $peruse->auth_link();
	} 

	function peruse_logout_link() {

		return current_url() . '?logout';
	}

	function post_product($body){
		global $peruse;

		return $peruse->post_product($body);
	}

	function peruse_current_user() {
		global $peruse;

		return $peruse->user();
	}

	function peruse_user_order() {
		global $peruse;

		return $peruse->order();
	}

	function peruse_post_auth($postId, $product_type) {
		global $peruse;

		$user_logged_in = peruse_current_user();

		if(!$user_logged_in){
			return false;
		}

		$has_product = $peruse->product($postId, $product_type);

		if($has_product->data && $has_product->data->authorized){
			return true;
		}

		return false;

	}

?>