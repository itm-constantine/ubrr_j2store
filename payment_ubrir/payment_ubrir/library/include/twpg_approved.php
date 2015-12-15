<?php
/**
 * @package	J2Store payment module for Joomla!
 * @version	1.0.0
 * @author	itmosfera.ru
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if(false) {
  defined('_JEXEC') or die('Restricted access'); 
}
					$bankHandler = new Ubrir(array(																											 // инициализируем объект операции в TWPG
							'shopId' => CSalePaySystemAction::GetParamValue("ID"), 
							'order_id' => CSalePaySystemAction::GetParamValue("ORDER_ID"), 
							'sert' => CSalePaySystemAction::GetParamValue("SERT"),
						    'twpg_order_id' => $arOrder['PS_STATUS_DESCRIPTION'], 
						    'twpg_session_id' => $arOrder['PS_STATUS_MESSAGE']
							));
					if($bankHandler->check_status("APPROVED")) {
					CSaleOrder::Update(CSalePaySystemAction::GetParamValue("ORDER_ID"), array("PAYED" => "Y"));   									
					CSaleOrder::StatusOrder(CSalePaySystemAction::GetParamValue("ORDER_ID"), "P");
					echo '<div class="ubr_s">Оплата успешно совершена</div>';
					}
					else echo '<div class="ubr_f">Неверный статус заказа</div>';
					
?>

