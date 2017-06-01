$(function() {

	var msg_array = {
    confirm_email: 'A link has been sent to your email to confirm the purchase of this product.',
    need_payment: 'Please add payment information <a href="#">here</a> to continue.',
    has_product: 'It appears that you already own this product!'
  }

   
 	$('.buy_article').click(function(e){
    e.preventDefault();

    $(e.target).text('One moment please...');

    //Load json data to get product for purchase
    $.getJSON( "sample_articles.json", function( data ) {
		  
    	var product_id = $(e.target).attr('data-id');
    	var product = null;

		  for(var i = 0; i<data.length; i++){
		  	if(product_id == data[i].id){
		  		product = data[i];
		  	}
		  }

		  product.product_url = window.location.href;

		  $.ajax({
        url : 'https://app.goperuse.com/api/graph/v1/product',
        type : 'POST',
        headers: { "Authorization": access_token},
        data : JSON.stringify(product),
        contentType: 'application/json',
        success : function(response) {
          if(response){

            if(!response.product.authorized){
              $(e.target).hide();
              $('#peruse_message').html(msg_array[response.code]);
              return;
            }
            
            if(response.code === 'has_product'){
            	$('#peruse_message-'+product.id).html(msg_array[response.code]);
            	return;
            }

            location.reload();
          }
        },
        error: function(error){
          console.log(error);
        }
	    });

		});

    

  });

});