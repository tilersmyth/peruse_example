<?php require_once('connect.php'); ?>
<!DOCTYPE html>
<html>
<head>
	<title>Peruse Example</title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

	<?php 

		$access_token = peruse_user_token();
		$peruse_user = peruse_current_user(); 
		$login_link = peruse_login_link();
		$logout_link = peruse_logout_link();
		$order = peruse_user_order();


		if(!$peruse_user){
			echo '<h3><a href="'.$login_link.'">Login to the Peruse demo!</a></h3>';
		}else{

			echo '<h3 style="margin-left:15px">Hi ' . $peruse_user->first_name . ', your balance is $' . $peruse_user->balance . '! (<a href="' . $logout_link . '">Logout</a>)</h3>'; 


			echo '<h4>Buy my articles:</h4>';
		
			$sample_articles = json_decode(file_get_contents('sample_articles.json'));

			foreach ($sample_articles as $article) {
			    echo '<div class="article"><h5>' . $article->title . '</h5><p>' . $article->content . '</p>';

			    if($_GET['pId'] == $article->id){

					switch ($_GET['status'] ) {
					    case "has_product":
					        echo "<span class='warning'>You already own this product!</span>";
					        break;
					    case "confirm_email":
					        echo "<span class='danger'>Confirm product purchase via email</span>";
					        break;
					    case "need_payment":
					        echo "<span class='danger'>Enter payment method to continue</span>";
					        break;
					    default:
					        echo "<span class='success'>Thank you for your purchase!</span>";
					}

			    }else{

			    	echo '<a href="'. current_url() .'action.php?pId='. $article->id .'">Buy article for $' . $article->regular_price . '</a>';

			    }

			    echo '</div>';

			    	
			} 
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