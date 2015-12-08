<?php

defined('_JEXEC') or die;
//require(dirname(__FILE__).'/../library/UbrirClass.php');
require(dirname(__FILE__).'/style.php');

class JFormFieldGetUbrir extends JFormField {

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $type = 'getUbrir';

	function getInput() {
		
		$mname = 'ubrir';
		$conf = new JConfig; 
						$db_conn = new mysqli($conf->host, $conf->user, $conf->password, $conf->db);
							if (mysqli_connect_errno()) {
							printf("Ошибка доступа к БД: %s\n", mysqli_connect_error());
						exit();
						}
		$settingsyeah = $db_conn->query('SELECT * FROM '.$conf->dbprefix.'extensions WHERE name="ubrir"' )->fetch_assoc();			
			
		$settingsyeah2 = json_decode($settingsyeah["params"], true );
		$order_id = '';
		$out = '';
		
		
		 if(!empty($_GET['task_ubrir']))
			switch ($_GET['task_ubrir']) {
				case '1':
					if(!empty($_GET['shoporderidforstatus']) AND !empty($settingsyeah2["twpg_id"])  AND !empty($settingsyeah2["twpg_sert"])) {
						$order_id = $_GET['shoporderidforstatus'];
					
						$answer = $db_conn->query('SELECT * FROM '.$conf->dbprefix.'j2store_orders WHERE order_id="'.$order_id.'"' )->fetch_assoc();			
						if(!empty($answer['transaction_details'])) {
							$bankHandler = new Ubrir(array(																												 // для статуса
								'shopId' => $settingsyeah2["twpg_id"],
								'order_id' => $order_id, 
								'sert' => $settingsyeah2["twpg_sert"],
								'twpg_order_id' => $answer['transaction_id'], 
								'twpg_session_id' =>$answer['transaction_details']
								));
							$out = '<div class="ubr_s">Статус заказа - '.$bankHandler->check_status().'</div>';	
						}
						else $out = '<div class="ubr_f">Получить статус данного заказа невозможно. Либо его не существует, либо он был оплачен через Uniteller</div>';	
					}
					if(empty($_GET['shoporderidforstatus'])) $out = '<div class="ubr_f">Вы не ввели номер заказа</div>';	
					break;
					
				case '2':
					if(!empty($_GET['shoporderidforstatus']) AND !empty($settingsyeah2["twpg_id"])  AND !empty($settingsyeah2["twpg_sert"])) {
						$order_id = $_GET['shoporderidforstatus'];
						
						$answer = $db_conn->query('SELECT * FROM '.$conf->dbprefix.'j2store_orders WHERE order_id="'.$order_id.'"' )->fetch_assoc();
						
						if(!empty($answer['transaction_details'])) {
							$bankHandler = new Ubrir(array(																												 // для детализации
								'shopId' => $settingsyeah2["twpg_id"],
								'order_id' => $order_id, 
								'sert' => $settingsyeah2["twpg_sert"],
								'twpg_order_id' => $answer['transaction_id'], 
								'twpg_session_id' =>$answer['transaction_details']
								));
							$out = $bankHandler->detailed_status();	
						}
						else $out = '<div class="ubr_f">Получить детализацию данного заказа невозможно. Либо его не существует, либо он был оплачен через Uniteller</div>';	
					}
					if(empty($_GET['shoporderidforstatus'])) $out = '<div class="ubr_f">Вы не ввели номер заказа</div>';	
					break;
					
				case '3':
					if(!empty($_GET['shoporderidforstatus']) AND !empty($settingsyeah2["twpg_id"])  AND !empty($settingsyeah2["twpg_sert"])) {
						$order_id = $_GET['shoporderidforstatus'];
						
						$answer = $db_conn->query('SELECT * FROM '.$conf->dbprefix.'j2store_orders WHERE order_id="'.$order_id.'"' )->fetch_assoc();
						if($answer['order_state_id'] == 1) {
							if(!empty($answer['transaction_details'])) {
								$bankHandler = new Ubrir(array(																												 // для реверса
									'shopId' => $settingsyeah2["twpg_id"],
								'order_id' => $order_id, 
								'sert' => $settingsyeah2["twpg_sert"],
								'twpg_order_id' => $answer['transaction_id'], 
								'twpg_session_id' =>$answer['transaction_details']
								));
								$res = $bankHandler->reverse_order();	
								if($res == 'OK') {
									$out = '<div class="ubr_s">Оплата успешно отменена</div>';
									$db_conn->query('UPDATE '.$conf->dbprefix.'j2store_orders SET `order_state_id` = 6 WHERE order_id="'.$_GET['shoporderidforstatus'].'"' );
								}
								else $out = $res;
							}
						else $out = '<div class="ubr_f">Получить реверс данного заказа невозможно. Он был оплачен через Uniteller</div>';
						}
						else $out = '<div class="ubr_f">Получить реверс данного заказа невозможно, он не был оплачен, либо его не существует</div>';
					}
					if(empty($_GET['shoporderidforstatus'])) $out = '<div class="ubr_f">Вы не ввели номер заказа</div>';	
					break;

				case '4':
					if(!empty($settingsyeah2["twpg_id"])  AND !empty($settingsyeah2["twpg_sert"])) {					
							$bankHandler = new Ubrir(array(																												 // для сверки итогов
								'shopId' => $settingsyeah2["twpg_id"],
								'sert' => $settingsyeah2["twpg_sert"],
								));
							$out = $bankHandler->reconcile();
					}                                                                                          
					break;		
					
				case '5':
					if(!empty($settingsyeah2["twpg_id"])  AND !empty($settingsyeah2["twpg_sert"])) {					
							$bankHandler = new Ubrir(array(																												 // для журнала операции
								'shopId' => $settingsyeah2["twpg_id"],
								'sert' => $settingsyeah2["twpg_sert"],
								));
							$out = $bankHandler->extract_journal();
					}      
					break;	

				case '6':
					if(!empty($settingsyeah2["uni_login"])  AND !empty($settingsyeah2["uni_emp"])) {					
							$bankHandler = new Ubrir(array(																												 // для журнала Uniteller
								'uni_login' => $settingsyeah2["uni_login"],
								'uni_pass' => $settingsyeah2["uni_emp"],
								));
							$out = $bankHandler->uni_journal();
					}     
					break;	
				case '7':
					if(!empty($_GET['mailsubject'])  AND !empty($_GET['maildesc'])) {					
							$to = 'info@itmosfera.ru';
							 $subject = htmlspecialchars($_GET['mailsubject'], ENT_QUOTES);
							 $message = 'Отправитель: '.htmlspecialchars($_GET['mailem'], ENT_QUOTES).' | '.htmlspecialchars($_GET['maildesc'], ENT_QUOTES);
							 $headers = 'From: '.$_SERVER["HTTP_HOST"];
							 mail($to, $subject, $message, $headers);
					}     
					break;			
					
				default:
					break;
			}
			else {
				$out = null;
				$order_id = null;
			}
			
			$toprint = '
			<div id="callback" style="height: 380px; display: none;">
			 <table>
			 <tr>
			 <h2 onclick="show(this);" style="text-align: center; cursor:pointer;">Обратная связь<span style="margin-left: 20px; font-size: 80%; color: grey;" onclick="jQuery(\'#callback\').toggle();">[X]</span></h2>
			 </tr>
			<tr>
         <td>Тема</td>
            <td>
            <select name="subject" id="mailsubject" style="width:150px">
              <option selected disabled>Выберите тему</option>
              <option value="Подключение услуги">Подключение услуги</option>
              <option value="Продление Сертификата">Продление Сертификата</option>
              <option value="Технические вопросы">Технические вопросы</option>
              <option value="Юридические вопросы">Юридические вопросы</option>
			  <option value="Бухгалтерия">Бухгалтерия</option>
              <option value="Другое">Другое</option>
            </select>
            </td>
          </tr>
 <tr>
 <td>Телефон</td>
 <td>
 <input type="text" name="mailem" id="mailem" style="width:150px">
 </td>
 </tr>
 <tr>
			 <td>Сообщение</td>
			 <td>
			 <textarea name="maildesc" id="maildesc" cols="30" rows="10" style="width:150px;resize:none;"></textarea>
			 </td>
			 </tr>
			 <tr><td></td>
			 <td><input id="sendmail" onclick="
			 var mailsubject = jQuery(\'#mailsubject\').val();
			 var maildesc = jQuery(\'#maildesc\').val();
			 var mailem = jQuery(\'#mailem\').val();
			 console.log(mailsubject);
			 console.log(maildesc);
			 console.log(mailem);
			 if(!mailem & !!maildesc) {
			 jQuery(\'#mailresponse\').html(\'<br>Необходимо указать телефон\');
			 return false;
			 }
			 if(!maildesc & !!mailem) {
			 jQuery(\'#mailresponse\').html(\'<br>Сообщение не может быть пустым\');
			 return false;
			 }
			 if(!!mailem & !!maildesc) 
			 jQuery.ajax({
			 type: \'POST\',
			 url: location.href,
			 data: {mailsubject:mailsubject, maildesc:maildesc, mailem:mailem, task_ubrir:7},
			 success: function(response){
			 jQuery(\'#mailresponse\').html(\'Письмо отправлено на почтовый сервер\');
			 jQuery(\'#maildesc\').val(null);
			 jQuery(\'#mailsubject\').val(null);
			 jQuery(\'#mailem\').val(null);
			 }
			 });
			 else jQuery(\'#mailresponse\').html(\'<br>Заполнены не все поля\');
			 return false;
			 " type="button" name="sendmail" value="Отправить">
			 </tr>
			 <tr>
			 <td>
			 </td>
			 <td style="padding: 0" id="mailresponse">
			 </td>
			 </tr>
			 <tr>
			 <td></td>
			<td>8 (800) 1000-200</td></tr>
			 </table>
			 </div>
			 
			<div style="width: 100%; margin-top: 10px;">'.$out.'</div>
			<div style="margin: 20px 0 20px 0; text-align: center; padding: 20px; width: 415px; border: 1px dashed #999;"> 
			<h3 style="text-align: center; padding: 0 0 20px 0; margin: 0;">Получить детальную информацию:</h3>
			<div style="margin: 0 auto; text-align: center; padding: 5px; width: 200px; border: 1px dashed #999;">Номер заказа: <br>
			<input style="margin: 5px; width: 150px;" type="text" name="shoporderidforstatus" id="shoporderidforstatus" value="'.$order_id.'" placeholder="№ заказа" size="8">
			<input style="margin: 5px;" type="hidden" name="task_ubrir" id="task_ubrir" value="">
			  <input class="twpginput" type="button" onclick="document.location = document.location+\'&task_ubrir=1&shoporderidforstatus=\'+jQuery(\'#shoporderidforstatus\').val()" id="statusbutton" value="Запросить статус">
			  <input class="twpginput" type="button" onclick="document.location = document.location+\'&task_ubrir=2&shoporderidforstatus=\'+jQuery(\'#shoporderidforstatus\').val()" id="detailstatusbutton" value="Детальная информация">
			  <input class="twpginput" type="button" onclick="document.location = document.location+\'&task_ubrir=3&shoporderidforstatus=\'+jQuery(\'#shoporderidforstatus\').val()" id="reversbutton" value="Вернуть деньги"><br>
			</div>  
			  <input class="twpgbutton" type="button" onclick="document.location = document.location+\'&task_ubrir=4\'" id="recresultbutton" value="Сверка итогов">
			  <input class="twpgbutton" type="button" onclick="document.location = document.location+\'&task_ubrir=5\'" id="journalbutton" value="Журнал операций TWPG">
			  <input class="twpgbutton" type="button" onclick="document.location = document.location+\'&task_ubrir=6\'" id="unijournalbutton" value="Журнал операций Uniteller">
			  <input class="twpgbutton" type="button" onclick="jQuery(\'#callback\').toggle()" id="unijournalbutton" value="Написать в банк">
			</div>
			';			

			
		return $toprint;
	}


}