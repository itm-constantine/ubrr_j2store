<?php	
		require('../../../../../configuration.php');
		$url = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
		$root = substr($url, 0, strpos($url, '/index.php' ));
		$index = $root.'/index.php';
		
		if (isset($_POST["xmlmsg"])) {
	
		if(stripos($url, "?")) $amp = "&"; else $amp = "?";
		if(stripos($_POST["xmlmsg"], "CANCELED") != false)  header("Location: ".$index."?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=payment_ubrir&result=3&on=" . $_GET['id']);
		else {
			
		  $xml_string = base64_decode($_POST["xmlmsg"]);
		  $parse_it = simplexml_load_string($xml_string);
		   
		  if ($parse_it->OrderStatus[0]=="DECLINED") header("Location: ".$index."?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=payment_ubrir&result=4&on=" . $_GET['id']);
		  if ($parse_it->OrderStatus[0]=="APPROVED") header("Location: ".$index."?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=payment_ubrir&result=2&on=" . $_GET['id']);
		 
		};
		};
		if(isset($_GET["ORDER_IDP"])) {
			header("Location: ".$index."?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=payment_ubrir&result=".$_GET['status']."&on=" . $_GET['id']);
		};
	
		?>