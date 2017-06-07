<?php
require_once('connect.php');

if(isset($_GET['pId'])){

	try {


		$referrer = str_replace("action.php", "", current_url());
	
		$sample_articles = json_decode(file_get_contents('sample_articles.json'));

		$selected_article;

		//Get applicable article
		foreach ($sample_articles as $article) {
		    if($_GET['pId'] == $article->id){
		    	$selected_article = $article;
		    }
		} 

		if(!$selected_article){
			throw new Exception("Product not found.");
		}

		$selected_article->product_url = $referrer;

		$post_product = post_product($selected_article);

		if($post_product->code){

			$redirect = $referrer . 'page.php?pId=' . $selected_article->id . '&status=' . $post_product->code;

			header('Location:' . filter_var($redirect, FILTER_SANITIZE_URL));
			die();
		}

	} catch (Exception $e) {
    echo 'Error: ',  $e->getMessage(), "\n";
	}

}

?>