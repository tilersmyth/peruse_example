<?php 
require_once('connect.php');
$referrer = str_replace("page.php", "", current_url());
if(empty($_GET['pId'])){
	header('Location:' . filter_var($referrer, FILTER_SANITIZE_URL));
	die();
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Peruse Example</title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

	<?php 
	try {

		//DB QUERY SIMULATION 
		$sample_articles = json_decode(file_get_contents('sample_articles.json'));
		$selected_article;
		foreach ($sample_articles as $article) {
		    if($_GET['pId'] == $article->id){
		    	$selected_article = $article;
		    }
		} 

		if(!$selected_article){
			throw new Exception("No article found with that ID!");
		}

		$access_token = peruse_user_token();
		$peruse_user = peruse_current_user(); 
		$login_link = peruse_login_link();
		$logout_link = peruse_logout_link();
		$order = peruse_user_order();
		$post_auth = peruse_post_auth($selected_article->id, 'Article');

		echo '<div class="article_single"><h2>' . $selected_article->title . '</h2>';

		if(!$post_auth){

			echo $selected_article->excerpt . '<br><br><a href="'. $referrer .'action.php?pId='. $selected_article->id .'">Buy article for $' . $selected_article->regular_price . '</a>';

		}else{
			echo $selected_article->content;
		}
		
		echo '</div>';

		echo '<br><br><a href="' . $referrer.'">Back to articles</a>';

	} catch (Exception $e) {
    echo 'Error: ',  $e->getMessage(), "\n";
    die();
	}

	?>


	<div id="dump">
		<h3>Dumping grounds:</h3>
		<div><b>Access token:</b> <?php echo '<pre>' . json_encode(isset($access_token) ? $access_token : null) . '</pre>'; ?></div><br>
		<div><b>User:</b> <?php echo '<pre>' . json_encode($peruse_user ? $peruse_user : null, JSON_PRETTY_PRINT) . '</pre>'; ?></div><br>
		<div><b>Order:</b> <?php echo '<pre>' . json_encode($order->data ? $order->data : null, JSON_PRETTY_PRINT) . '</pre>'; ?></div>
	</div>
</body>
</html>