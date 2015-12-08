<?php
		if(!empty($response_order)) {	
		$arFields = array( "PS_STATUS_DESCRIPTION" => $response_order->OrderID[0], "PS_STATUS_MESSAGE" => $response_order->SessionID[0]);
		$is_updated = CSaleOrder::Update(CSalePaySystemAction::GetParamValue("ORDER_ID"), $arFields);	                                     // сохраняем данные об обмене данными с банком
		if(!$is_updated) throw new UbrirException(sprintf('Произошла внутрення ошибка сайта. Приносим свои извинения'));
		else $readyToPay = true;	
		}
		else throw new UbrirException(sprintf('Система оплаты на данный момент не способна провести платеж. Принсим извинения за неудобства.'));
					
?>

