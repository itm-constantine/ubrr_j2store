<?php
					
	  $callbackurl = JURI::root () .'plugins/j2store/payment_ubrir/payment_ubrir/tmpl/result.php?id='.$data['order_id'];
	  $sign = strtoupper(md5(md5($this->params->get('uni_id', 'PLG_J2STORE_PAYMENT_UBRIR')).'&'.md5($this->params->get('uni_login', 'PLG_J2STORE_PAYMENT_UBRIR')).'&'.md5($this->params->get('uni_pass', 'PLG_J2STORE_PAYMENT_UBRIR')).'&'.md5($data['order_id']).'&'.md5($twpg_amount)));
	  echo '<form action="https://91.208.121.201/estore_listener.php" name="uniteller" method="post">
		<input type="hidden" name="SHOP_ID" value="'.$this->params->get('uni_id', 'PLG_J2STORE_PAYMENT_UBRIR').'" >
		<input type="hidden" name="LOGIN" value="'.$this->params->get('uni_login', 'PLG_J2STORE_PAYMENT_UBRIR').'" >
		<input type="hidden" name="ORDER_ID" value="'.$data['order_id'].'">
		<input type="hidden" name="PAY_SUM" value="'.$twpg_amount.'" >
		<input type="hidden" name="VALUE_1" value="'.$data['order_id'].'" >
		<input type="hidden" name="URL_OK" value="'.$callbackurl.'&status=1&" >
		<input type="hidden" name="URL_NO" value="'.$callbackurl.'&status=0&" >
		<input type="hidden" name="SIGN" value="'.$sign.'" >
		<input type="hidden" name="LANG" value="RU" >
	  </form>';
					
?>

