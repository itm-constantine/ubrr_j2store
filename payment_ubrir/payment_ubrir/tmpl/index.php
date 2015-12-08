<?php	
		//require('../../../configuration.php');
		define( '_JEXEC', 1 );
define( 'DS', DIRECTORY_SEPARATOR );
define( 'JPATH_BASE', $_SERVER[ 'DOCUMENT_ROOT' ] );
require_once( JPATH_BASE . DS . 'includes' . DS . 'defines.php' );
require_once( JPATH_BASE . DS . 'includes' . DS . 'framework.php' );
require_once( JPATH_BASE . DS . 'libraries' . DS . 'joomla' . DS . 'factory.php' );
$app = JFactory::getApplication('site');

// Execute the application.
$app->execute();
		
		
		/* $url = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
		$root = substr($url, 0, strpos($url, '/plugins' ));
		$index = $root.'/index.php';
		
		if (isset($_POST["xmlmsg"])) {
	
		if(stripos($url, "?")) $amp = "&"; else $amp = "?";
		if(stripos($_POST["xmlmsg"], "CANCELED") != false)  header("Location: ".$index."?option=com_virtuemart&view=pluginresponse&task=pluginnotification&result=3&on=" . $_GET['id']);
		else {
			
		  $xml_string = base64_decode($_POST["xmlmsg"]);
		  $parse_it = simplexml_load_string($xml_string);
		   
		  if ($parse_it->OrderStatus[0]=="DECLINED") header("Location: ".$index."?option=com_virtuemart&view=pluginresponse&task=pluginnotification&result=4&on=" . $_GET['id']);
		  if ($parse_it->OrderStatus[0]=="APPROVED") header("Location: ".$index."?option=com_virtuemart&view=pluginresponse&task=pluginnotification&result=2&on=" . $_GET['id']);
		 
		};
		};
		if(isset($_GET["ORDER_IDP"])) {
			header("Location: ".$index."?option=com_virtuemart&view=pluginresponse&task=pluginnotification&result=".$_GET['status']."&on=" . $_GET['id']);
		}; */
		?>