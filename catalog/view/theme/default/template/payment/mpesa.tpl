<h2><?php echo $text_instruction; ?></h2>
<div class="content">
  <p><?php echo $text_description; ?></p>
  <p><?php echo $mpesa; ?></p>
  <p><?php echo $text_payment; ?></p>
  <hr/>
  <p> M-Pesa Transaction Code: <input type="text" name="mpesa_code" value="" /> </p>
</div>
<div class="buttons">
  <div class="right">
    <input type="button" onclick="sendcode()" value="<?php echo $button_confirm; ?>" id="button-confirm" class="button" />
  </div>
</div>
<script type="text/javascript"><!--
	function sendcode(){
		var mpesa_code = jQuery('input[name=mpesa_code]').val();
		mpesa_code = mpesa_code.toUpperCase();
		if(confirm('Are you Sure '+mpesa_code+' is the correct Transaction Code? ')){
			jQuery(this).text('Working...');
			jQuery.post('index.php?route=payment/mpesa/mpesa', {code: mpesa_code}, function(data, textStatus, xhr) {
			  if(data == 'success') {
				$.ajax({ 
					type: 'post',
					url: 'index.php?route=payment/mpesa/confirm',
					success: function(data) {
						location = '<?php echo $continue; ?>';
					}		
				});
			  } else {
			  	jQuery(this).text('Confirm Order');
			  	alert( data+ ' Error Saving ' );
			  }
			});
		} else{
			return false;
		}
	}
//--></script> 
<style type="text/css" media="screen">
input[name=mpesa_code]	{
	text-transform: uppercase;
}
</style>
