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

$task_ubrir = JRequest::getVar('task_ubrir');
$shoporderidforstatus = JRequest::getVar('shoporderidforstatus');
$VALUE2_ID_1 = JRequest::getVar('VALUE2_ID_1');
$VALUE2_SERT_1 = JRequest::getVar('VALUE2_SERT_1');
$VALUE2_UNI_LOGIN_1 = JRequest::getVar('VALUE2_UNI_LOGIN_1');
$VALUE2_UNI_EMP_1 = JRequest::getVar('VALUE2_UNI_EMP_1');
    if(!empty($task_ubrir))
	switch ($task_ubrir) {
				case '1':
					if(!empty($shoporderidforstatus) AND !empty($VALUE2_ID_1)  AND !empty($VALUE2_SERT_1)) {
						$order_id = $shoporderidforstatus*1;
						$arOrder = CSaleOrder::GetByID($order_id);
						if(!empty($arOrder['PS_STATUS_MESSAGE'])) {
							$bankHandler = new Ubrir(array(																												 // для статуса
								'shopId' => $VALUE2_ID_1,
								'order_id' => $order_id, 
								'sert' => $VALUE2_SERT_1,
								'twpg_order_id' => $arOrder['PS_STATUS_DESCRIPTION'], 
								'twpg_session_id' => $arOrder['PS_STATUS_MESSAGE']
								));
							$out = '<div class="ubr_s">Статус заказа - '.$bankHandler->check_status().'</div>';	
						}
						else $out = '<div class="ubr_f">Получить статус данного заказа невозможно. Либо его не существует, либо он был оплачен через Uniteller</div>';	
					}
					break;
					
				case '2':
					if(!empty($shoporderidforstatus) AND !empty($VALUE2_ID_1)  AND !empty($VALUE2_SERT_1)) {
						$order_id = $shoporderidforstatus*1;
						$arOrder = CSaleOrder::GetByID($order_id);
						if(!empty($arOrder['PS_STATUS_MESSAGE'])) {
							$bankHandler = new Ubrir(array(																												 // для детализации
								'shopId' => $VALUE2_ID_1,
								'order_id' => $order_id, 
								'sert' => $VALUE2_SERT_1,
								'twpg_order_id' => $arOrder['PS_STATUS_DESCRIPTION'], 
								'twpg_session_id' => $arOrder['PS_STATUS_MESSAGE']
								));
							$out = $bankHandler->detailed_status();	
						}
						else $out = '<div class="ubr_f">Получить детализацию данного заказа невозможно. Либо его не существует, либо он был оплачен через Uniteller</div>';	
					}
					break;
					
				case '3':
					if(!empty($shoporderidforstatus) AND !empty($VALUE2_ID_1) AND !empty($VALUE2_SERT_1)) {
						$order_id = $shoporderidforstatus*1;
						$arOrder = CSaleOrder::GetByID($order_id);
						if($arOrder['PAYED'] == 'Y') {
							if(!empty($arOrder['PS_STATUS_MESSAGE'])) {
								$bankHandler = new Ubrir(array(																												 // для реверса
									'shopId' => $VALUE2_ID_1,
									'order_id' => $order_id, 
									'sert' => $VALUE2_SERT_1,
									'twpg_order_id' => $arOrder['PS_STATUS_DESCRIPTION'], 
									'twpg_session_id' => $arOrder['PS_STATUS_MESSAGE']
								));
								$res = $bankHandler->reverse_order();	
								if($res == 'OK') {
									$out = '<div class="ubr_s">Оплата успешно отменена</div>';
									CSaleOrder::Update($order_id, array("PAYED" => "N"));   									
									CSaleOrder::StatusOrder($order_id, "N");
								}
								else $out = $res;
							}
						else $out = '<div class="ubr_f">Получить реверс данного заказа невозможно. Он был оплачен через Uniteller</div>';
						}
						else $out = '<div class="ubr_f">Получить реверс данного заказа невозможно, он не был оплачен, либо его не существует</div>';
					}
					break;

				case '4':
					if(!empty($VALUE2_ID_1)  AND !empty($VALUE2_SERT_1)) {					
							$bankHandler = new Ubrir(array(																												 // для сверки итогов
								'shopId' => $VALUE2_ID_1,
								'sert' => $VALUE2_SERT_1,
								));
							$out = $bankHandler->reconcile();
					}                                                                                          
					break;		
					
				case '5':
					if(!empty($VALUE2_ID_1)  AND !empty($VALUE2_SERT_1)) {					
							$bankHandler = new Ubrir(array(																												 // для журнала операции
								'shopId' => $VALUE2_ID_1,
								'sert' => $VALUE2_SERT_1,
								));
							$out = $bankHandler->extract_journal();
					}      
					break;	

				case '6':
					if(!empty($VALUE2_UNI_LOGIN_1)  AND !empty($VALUE2_UNI_EMP_1)) {					
							$bankHandler = new Ubrir(array(																												 // для журнала Uniteller
								'uni_login' => $VALUE2_UNI_LOGIN_1,
								'uni_pass' => $VALUE2_UNI_EMP_1,
								));
							$out = $bankHandler->uni_journal();
					}     
					break;				
					
				default:
					break;
			}
			
$toprint = '
 <div style="width: 100%; margin-top: 10px;">'.$out.'</div>
<div style="margin: 20px 0 20px 0; text-align: center; padding: 20px; width: 415px; border: 1px dashed #999;"> 
<h3 style="text-align: center; padding: 0 0 20px 0; margin: 0;">Получить детальную информацию:</h3>
<div style="margin: 0 auto; text-align: center; padding: 5px; width: 200px; border: 1px dashed #999;"><form action="" method="post">Номер заказа: <br>
<input style="margin: 5px;" type="text" name="shoporderidforstatus" id="shoporderidforstatus" value="'.$order_id.'" placeholder="№ заказа" size="8">
<input style="margin: 5px;" type="hidden" name="task_ubrir" id="task_ubrir" value="">
      <input class="twpginput" type="button" onclick="$(\'#task_ubrir\').val(1); submit();" id="statusbutton" value="Запросить статус">
      <input class="twpginput" type="button" onclick="$(\'#task_ubrir\').val(2); submit();" id="detailstatusbutton" value="Детальная информация">
      <input class="twpginput" type="button" onclick="$(\'#task_ubrir\').val(3); submit();" id="reversbutton" value="Вернуть деньги"><br>
 </div>  
      <input class="twpgbutton" type="button" onclick="$(\'#task_ubrir\').val(4); submit();" id="recresultbutton" value="Сверка итогов">
      <input class="twpgbutton" type="button" onclick="$(\'#task_ubrir\').val(5); submit();" id="journalbutton" value="Журнал операций TWPG">
	  <input class="twpgbutton" type="button" onclick="$(\'#task_ubrir\').val(6); submit();" id="unijournalbutton" value="Журнал операций Uniteller"></form>
</div>
';			

?>
