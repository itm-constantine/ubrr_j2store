<?php
/**
 * @package	J2Store payment module for Joomla!
 * @version	1.0.0
 * @author	itmosfera.ru
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if (false) {
	defined('_JEXEC') or die('Restricted access');
}	
$xmlmsg = $_POST['xmlmsg'];
$ORDER_IDP = $_GET['ORDER_IDP'];
$status = $_GET['status'];
$id = $_GET['id'];

		require('../../../../../configuration.php');
		$url = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
		$root = substr($url, 0, strpos($url, '/index.php' ));
		$index = $root.'/index.php';
		
		if (isset($xmlmsg)) {
	
		if(stripos($url, "?")) $amp = "&"; else $amp = "?";
		if(stripos($xmlmsg, "CANCELED") != false)  header("Location: ".$index."?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=payment_ubrir&result=3&on=" . $id);
		else {
			
		  $xml_string = base64_decode($xmlmsg);
		  $parse_it = simplexml_load_string($xml_string);
		   
		  if ($parse_it->OrderStatus[0]=="DECLINED") {
		   $xml_string = base64_decode($xmlmsg);
		  $parse_it = simplexml_load_string($xml_string); $desc = (string)$parse_it->ResponseDescription;
		  header("Location: ".$index."?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=payment_ubrir&result=4&desc=".$desc."&on=" . $id);
		  }
		  if ($parse_it->OrderStatus[0]=="APPROVED") header("Location: ".$index."?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=payment_ubrir&result=2&on=" . $id);
		 
		};
		};
		if(isset($ORDER_IDP)) {
			header("Location: ".$index."?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=payment_ubrir&result=".$status."&on=" . $id);
		};
	
		?>