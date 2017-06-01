<?php

	/**
	* 
	*/
	class Peruse {

		private static $baseUrl = 'https://app.goperuse.com/'; 

		private $user_token;

		private $auth_link;

		private $peruse_user;

		private $product;

		private $order;


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

			define('CLIENT_ID', 'XXXXXXXXXXXXXXXX');
			define('CLIENT_SECRET', 'XXXXXXXXXXXXXXXX');
			define('REDIRECT_URI', 'XXXXXXXXXXXXXXXX');

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

					$selected_scope = (!empty($this->peruse_options['scope']) ? $this->peruse_options['scope'] : []);
					$selected_scope_csv = implode(", ", $selected_scope);

			    $options = [
					   'scope' => $selected_scope_csv
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

					  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']; 

					  header('Location:' . filter_var($redirect, FILTER_SANITIZE_URL));
					  die();

				  } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

			      // Failed to get the access token or user details.
			      exit($e->getMessage());

				  }
				}

			}

		}

		public function user_token() { 

  		return $this->user_token;

  	}

		public function auth_link() { 

  		return $this->auth_link;

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

	function peruse_user_token() { 
		global $peruse;

		return $peruse->user_token();
	}

	function peruse_login_link() {
		global $peruse;

		return $peruse->auth_link();
	} 

	function peruse_logout_link() {

		$logout_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

		return $logout_url . '?logout';
	}

	function peruse_current_user() {
		global $peruse;

		return $peruse->user();
	}

	function peruse_user_order() {
		global $peruse;

		return $peruse->order();
	}

?>