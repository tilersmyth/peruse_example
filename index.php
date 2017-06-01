<?php require_once('connect.php'); ?>
<!DOCTYPE html>
<html>
<head>
	<title>Peruse Example</title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
	<h2>Basic Peruse Example</h2>

	<?php 

		$access_token = peruse_user_token();
		$peruse_user = peruse_current_user(); 
		$login_link = peruse_login_link();
		$logout_link = peruse_logout_link();
		$order = peruse_user_order();


		if(!$peruse_user){
			echo '<a href="'.$login_link.'">Login</a>';
		}else{

			echo '<p>Hi ' . $peruse_user->first_name . ', your balance is $' . $peruse_user->balance . '! (<a href="' . $logout_link . '">Logout</a>)</p>'; 


			echo '<h4>Buy my articles:</h4>';
		
			$sample_articles = json_decode(file_get_contents('sample_articles.json'));

			foreach ($sample_articles as $article) {
			    echo '<div class="article">
			    	<h4>' . $article->title . '</h4>
			    	<p>' . $article->content . '</p>
			    	<button class="buy_article" data-id="' . $article->id . '">buy article for $' . $article->regular_price . '</button>
			    	<div id="peruse_message-' . $article->id . '"></div>
			    	</div>';
			} 


		} 
	?>


	<div style="background-color:#eeeeee; margin-top: 200px; padding: 20px;">
		<h3>Dumping grounds:</h3>
		<div><b>Access token:</b> <?php var_dump(isset($access_token) ? $access_token : null); ?></div><br>
		<div><b>User:</b> <?php var_dump(isset($peruse_user) ? $peruse_user : null); ?></div><br>
		<div><b>Order:</b> <?php var_dump($order->data ? $order->data : null); ?></div>
	</div>
</body>

<script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
<script><?php echo 'var access_token = ' . json_encode($access_token) . ';'; ?></script>
<script src="script.js" type="text/javascript"></script>
</html>