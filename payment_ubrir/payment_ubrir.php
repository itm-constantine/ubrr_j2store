<?php
defined('_JEXEC') or die('Restricted access');

require(dirname(__FILE__).'/payment_ubrir/library/UbrirClass.php');

class plgJ2StorePayment_ubrir extends J2StorePaymentPlugin {

	const RELEASE = 'VM 3.0.9';
	const SU_ubrirBANKING = 'su';
	public $_element = 'payment_ubrir';
	private $public_key = '';
	private $private_key = '';
	public $code_arr = array();
	private $_isLog = false;
	var $_j2version = null;


	function __construct (& $subject, $config) {

		parent::__construct($subject, $config);
	
		$this->loadLanguage('', JPATH_ADMINISTRATOR);
		$this->code_arr = array ();

	}
	
	

	function _prePayment( $data ) {
	
		$app = JFactory::getApplication();
		$currency = J2Store::currency();

		// Prepare the payment form
		$vars = new JObject;
		$vars->url = JRoute::_("index.php?option=com_j2store&view=checkout");
		$vars->order_id = $data['order_id'];
		$vars->orderpayment_id = $data['orderpayment_id'];
		$vars->orderpayment_type = $this->_element;
		
		F0FTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
		$order = F0FTable::getInstance('Order', 'J2StoreTable');
		$order->load($data['orderpayment_id']);
		
		$twpg_amount = round($order->order_total, 2);
		$bankHandler = new Ubrir(array(				        // инициализируем объект операции в TWPG
							'shopId' => $this->params->get('twpg_id', 'PLG_J2STORE_PAYMENT_UBRIR'), 
							'order_id' => $data['order_id'], 
							'sert' => $this->params->get('twpg_sert', 'PLG_J2STORE_PAYMENT_UBRIR'), 
							'amount' => $twpg_amount,
							'approve_url' => JURI::root () .'plugins/j2store/payment_ubrir/payment_ubrir/tmpl/result.php?id='.$data['order_id'],
							'cancel_url' => JURI::root () .'plugins/j2store/payment_ubrir/payment_ubrir/tmpl/result.php?id='.$data['order_id'],
							'decline_url' => JURI::root () .'plugins/j2store/payment_ubrir/payment_ubrir/tmpl/result.php?id='.$data['order_id'],
							));                    
		$response_order = $bankHandler->prepare_to_pay();
		//var_dump($data['order_id']);
		 if(!empty($response_order)) {	
		$db =& JFactory::getDBO();
		$sql = " UPDATE #__j2store_orders 
		SET `transaction_id` = ".$response_order->OrderID[0].", `transaction_details` = '".$response_order->SessionID[0]."'
		WHERE `order_id` = ".$data['order_id'];
		$db->setQuery($sql);
		if(!$db->query()) exit('error_1101'); 
		}
		else exit('error_1102');  
	
		$out = '';
		$twpg_url = $response_order->URL[0].'?orderid='.$response_order->OrderID[0].'&sessionid='.$response_order->SessionID[0];
		$out .= '<p>Данный заказ необходимо оплатить одним из методов, приведенных ниже: </p> <INPUT TYPE="button" value="Оплатить Visa" onclick="document.location = \''.$twpg_url.'\'">';
		if($this->params->get('two', 'PLG_J2STORE_PAYMENT_UBRIR') == 0) {                                                                               // если активны два процессинга, то работаем еще и с Uniteller
	    $out .= ' <INPUT TYPE="button" onclick="document.forms.uniteller.submit()" value="Оплатить MasterCard">';
	    include(dirname(__FILE__)."/payment_ubrir/library/include/uni_form.php");
	  };
	  return $out;
	}


	
	function _postPayment( $data ) {
	
	switch ($_GET['result']) {
				case '0':
					return '<div style="padding: 5px;" class="alert-danger">Оплата не совершена</div>';                                                                                          //эти два пункта по Юнителлеру
					break;		
					
				case '1':
					return '<div style="padding: 5px;" class="alert-success" class="ubr_s">Оплата совершена успешно, ожидайте обработки заказа</div>';
					break;		
		
				case '3':
					return '<div style="padding: 5px;" class="alert-danger" class="ubr_f">Оплата отменена пользователем</div>';
					break;
					
				case '4':
					return '<div style="padding: 5px;" class="alert-danger" class="ubr_f">Оплата отменена банком</div>';
					break;
					
				case '2':
					$db =& JFactory::getDBO();
					$settingsyeah = 'SELECT * FROM #__extensions WHERE name="ubrir"';			
					$db->setQuery($settingsyeah);
					$current0 = $db->loadObjectList();
					$settingsyeah2 = json_decode($current0[0]->params, true );
				
					$db2 =& JFactory::getDBO();
					$sql = "SELECT * FROM #__j2store_orders WHERE order_id = '".htmlspecialchars(stripslashes($_GET['on']))."'";
					$db2->setQuery($sql);
					$current = $db2->loadObjectList();
					//var_dump($current); die;
					if(empty($current)) exit('error_1101'); 
					
					
					$bankHandler = new Ubrir(array(																											 // инициализируем объект операции в TWPG
							'shopId' => $settingsyeah2["twpg_id"], 
							'order_id' => $_GET['on'], 
							'sert' => $settingsyeah2["twpg_sert"],
						    'twpg_order_id' => $current[0]->transaction_id, 
						    'twpg_session_id' => $current[0]->transaction_details
							)); 
						
					if($bankHandler->check_status("APPROVED")) {
					$sql = " UPDATE #__j2store_orders 
					SET `order_state_id` = 1
					WHERE `order_id` = ".htmlspecialchars(stripslashes($_GET['on']));
					$db->setQuery($sql);
					if(!$db->query()) exit('error_1101'); 
					$out = '<div style="padding: 5px;" class="alert-success">Оплата успешно совершена</div>';
					}
					else $out = '<div style="padding: 5px;" class="alert-danger">Неверный статус заказа</div>';
					return $out;
					break;
				
				case '5':
					$db =& JFactory::getDBO();
					$sql = " UPDATE #__j2store_orders 
					SET `order_state_id` = 1
					WHERE `order_id` = ".htmlspecialchars(stripslashes($_GET['on']));
					$db->setQuery($sql);
					if(!$db->query()) exit('error_1101'); 
					return '<div style="padding: 5px;" class="alert-success">Оплата успешно совершена</div>';
					break;
					
				default:
					# code...
					break;
					
			}
		
	}


	

	
}

// No closing tag
