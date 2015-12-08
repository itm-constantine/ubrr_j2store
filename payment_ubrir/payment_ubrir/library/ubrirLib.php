<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @mainpage
 * Base class for ubrir XML-Api
 * This class implements basic http authentication and a xml-parser
 * for parsing response messages
 *
 * Requires libcurl and openssl
 *
 * Copyright (c) 2012 ubrir AG
 *
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 *
 * $Date: 2012-11-23 17:15:47 +0100 (Fri, 23 Nov 2012) $
 * @version ubrirLib 1.5.4  $Id: ubrirLib.php 5773 2012-11-23 16:15:47Z dehn $
 * @author ubrir AG http://www.ubrir.com (integration@ubrir.com)
 *
 */

if(!defined('ubrirLIB_VERSION')) {
	define('ubrirLIB_VERSION','1.5.4');
}

require_once dirname(__FILE__).'/ubrirLib_abstract.inc.php';
require_once dirname(__FILE__).'/ubrirLib_confirm_sr.inc.php';
require_once dirname(__FILE__).'/ubrirLib_edit_sr.inc.php';
require_once dirname(__FILE__).'/ubrirLib_cancel_sr.inc.php';
require_once dirname(__FILE__).'/ubrirLib_ideal_banks.inc.php';
require_once dirname(__FILE__).'/ubrirLib_debit.inc.php';
require_once dirname(__FILE__).'/ubrirLib_http.inc.php';
require_once dirname(__FILE__).'/ubrirLib_multipay.inc.php';
require_once dirname(__FILE__).'/ubrirLib_notification.inc.php';
require_once dirname(__FILE__).'/ubrirLib_refund.inc.php';
require_once dirname(__FILE__).'/ubrirLib_transaction_data.inc.php';
require_once dirname(__FILE__).'/ubrirLib_Logger.inc.php';

/** Include any available helper here **/
require_once dirname(__FILE__).'/helper/class.abstract_document.inc.php';
require_once dirname(__FILE__).'/helper/class.invoice.inc.php';
require_once dirname(__FILE__).'/helper/elements/ubrir_element.php';
require_once dirname(__FILE__).'/helper/elements/ubrir_tag.php';
require_once dirname(__FILE__).'/helper/elements/ubrir_html_tag.php';
require_once dirname(__FILE__).'/helper/elements/ubrir_text.php';
require_once dirname(__FILE__).'/helper/array_to_xml.php';
require_once dirname(__FILE__).'/helper/xml_to_array.php';


/**
 *
 * Basic PHP Library for communication with multipay API and related products of ubrir.com
 * @author payment-network.com
 *
 */
class ubrirLib {
	
	public $errorPos = 'global'; //or su, sr, sv...
	
	public $errors = array();
	
	public $warnings = array();
	
	public $enableLogging = false;
	
	public $errorCountTemp = 0;
	
	public $ubrirLibHttp = null;
	
	public $ubrirLibLogger = null;
	
	protected $_apiKey;
	
	protected $_userId;
	
	protected $_response;
	
	protected $_products = array('global', 'sr', 'su', 'sv', 'ls', 'sl', 'sf');
	
	private $_logfilePath = false;
	
	
	/**
	 * Constructor for ubrirLib
	 * @param string $apiKey
	 */
	public function __construct($userId = '', $apiKey = '', $apiUrl = '') {
		$this->_userId = $userId;
		$this->_apiKey = $apiKey;
		$this->ubrirLibHttp = new ubrirLib_Http($apiUrl, $this->_getHeaders());
		$this->ubrirLibLogger = new ubrirLibLogger();
		$this->enableLogging = (getenv('ubrirDebug') == 'true') ? true : false;
	}
	
	
	/**
	 * Getter for warnings
	 *
	 * @return empty array if no warnings exists ELSE array with warning-codes and warning-messages
	 * @public
	 */
	public function getWarnings($paymentMethod = 'all', $message = '') {
		if ($message == '') {
			$message = $this->warnings;
		} else {
			$message = $this->_parseErrorresponse($message);
		}
		
		$supportedPaymentMethods = $this->_products;
		
		if (!in_array($paymentMethod, $supportedPaymentMethods)) {
			$paymentMethod = 'all';
		}
		
		$returnArray = array();
		
		//return global + selected payment method
		foreach ($supportedPaymentMethods as $pm) {
			if (($paymentMethod == 'all' || $pm == 'global' || $paymentMethod == $pm) && array_key_exists($pm, $message)) {
				$returnArray = array_merge($returnArray, $message[$pm]);
			}
		}
		
		return $returnArray;
	}
	
	
	/**
	 * Getter for errors
	 *
	 * @param (optional) array $message response array
	 * @return emtpy array if no error exist ELSE array with error-codes and error-messages
	 * @public
	 */
	public function getErrors($paymentMethod = 'all', $message = '') {
		if ($message == '') {
			$message = $this->handleErrors($this->errors);
		} else {
			$message = $this->_parseErrorresponse($message);
		}
		
		if (!$this->isError($paymentMethod, $message)) {
			return array();
		}
		
		$supportedPaymentMethods = $this->_products;
		
		if (!in_array($paymentMethod, $supportedPaymentMethods)) {
			$paymentMethod = 'all';
		}
		
		$returnArray = array();
		
		//return global + selected payment method
		foreach ($supportedPaymentMethods as $pm) {
			if (($paymentMethod == 'all' || $pm == 'global' || $paymentMethod == $pm) && array_key_exists($pm, $message)) {
				$returnArray = array_merge($returnArray, $message[$pm]);
			}
		}
		
		return $returnArray;
	}
	
	
	/**
	 *
	 * Alter error array and set error message and error code together as one
	 * @param array $errors
	 */
	function handleErrors($errors) {
		$errorKeys = array_keys($errors);
		
		foreach($errorKeys as $errorKey) {
			$i = 0;
			
			foreach ($errors[$errorKey] as $partialError) {
				if (!empty($errors[$errorKey][$i]['field']) && $errors[$errorKey][$i]['field'] !== '') {
					$errors[$errorKey][$i]['code'] .= '.'.$errors[$errorKey][$i]['field'];
				}
				
				$i++;
			};
		}
		
		return $errors;
	}
	
	
	/**
	 * returns one error message
	 * @see getErrors() for more detailed errors
	 * @param array $message response array
	 * @return string errormessage ELSE false
	 * @public
	 */
	public function getError($paymentMethod = 'all', $message = '') {
		if ($message == '') {
			$message = $this->errors;
		} else{
			$message = $this->_parseErrorresponse($message);
		}
		
		$supportedPaymentMethods = $this->_products;
		
		if (!in_array($paymentMethod, $supportedPaymentMethods)) {
			$paymentMethod = 'all';
		}
		
		if (is_array($message)) {
			if ($paymentMethod == 'all') {
				foreach ($message as $key => $error) {
					if (is_array($error) && !empty($error)){
						return 'Error: '.$error[0]['code'].':'.$error[0]['message'];
					}
				}
			} else {
				foreach ($message as $key => $error) {
					if ($key != 'global' && $key != $paymentMethod) {
						continue;
					}
					
					if (is_array($error) && !empty($error)){
						return 'Error: '.$error[0]['code'].':'.$error[0]['message'];
					}
				}
			}
		}
		
		return false;
	}
	
	
	/**
	 *
	 * checks (response)-array for warnings
	 * @param array $message response array
	 * @param string $paymentMethod - 'all', 'sr', 'su', 'sv', 'sa', 'ls', 'sl', 'sf' (if unknown then it uses "all")
	 * @return boolean true if warnings found ELSE false
	 * @public
	 */
	public function isWarning($paymentMethod = 'all', $message = '') {
		if ($message == '') {
			$message = $this->warnings;
		}
		
		if (empty($message)) {
			return false;
		}
		
		if (!in_array($paymentMethod, $this->_products)) {
			$paymentMethod = 'all';
		}
		
		if ($paymentMethod == 'all') {
			if (is_array($message)) {
				foreach ($message as $error) {
					if (!empty($error)) {
						return true;
					}
				}
			}
		} else {
			//paymentMethod-specific search
			if (is_array($message)) {
				if ((isset($message[$paymentMethod]) && !empty($message[$paymentMethod])) ||
						(isset($message['global']) && !empty($message['global']))) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	
	/**
	 * checks (response)-array for error
	 * @param array $message response array
	 * @param string $paymentMethod - 'all', 'sr', 'su', 'sv', 'sa', 'ls', 'sl', 'sf' (if unknown then it uses "all")
	 * @return boolean true if errors found (in given payment-method or in 'global') ELSE false
	 * @public
	 */
	public function isError($paymentMethod = 'all', $message = '') {
		if ($message == '') {
			$message = $this->errors;
		}
		
		if (empty($message)) {
			return false;
		}
		
		if (!in_array($paymentMethod, $this->_products)) {
			$paymentMethod = 'all';
		}
		
		if ($paymentMethod == 'all') {
			if (is_array($message)) {
				foreach ($message as $error) {
					if (!empty($error)) {
						return true;
					}
				}
			}
		} else {
			//paymentMethod-specific search
			if (is_array($message)) {
				if ((isset($message[$paymentMethod]) && !empty($message[$paymentMethod])) ||
						(isset($message['global']) && !empty($message['global']))) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	
	/**
	 * set Errors
	 * later use getError(), getErrors() or isError() to retrieve them
	 * @param string $message - Detailinformationen about the error
	 * @param string $pos - Position in the errors-array, must be one of: 'global', 'sr', 'su', 'sv', 'sa', 'ls', 'sl', 'sf'
	 * @param string $errorCode - a number or string to specify the errors in the module
	 * @param string $field - if $errorCode deals with a field
	 */
	public function setError($message, $pos = 'global', $errorCode = '-1', $field = '') {
		$supportedErrorsPos = array('global', 'sr', 'su', 'sv', 'sa', 'ls', 'sl', 'sf');
		
		if (!in_array($pos, $supportedErrorsPos)) {
			$paymentMethod = 'global';
		}
		
		if (!isset($this->errors[$pos])) {
			$this->errors[$pos] = array();
		}
		
		$error = array ('code' => $errorCode, 'message' => $message, 'field' => $field);
		$this->errors[$pos][] = $error;
	}
	
	
	/**
	 * delete all warnings
	 * @public
	 */
	public function deleteAllWarnings() {
		$this->errorPos = 'global';
		$this->errorCountTemp = 0;
		$this->warnings = array();
	}
	
	
	/**
	 * delete all errors
	 * @public
	 */
	public function deleteAllErrors() {
		$this->errorPos = 'global';
		$this->errorCountTemp = 0;
		$this->errors = array();
	}
	
	
	/**
	 * internal send-method, will check http-errorcode and return body
	 * @param String $message message to post
	 * @return string error or body
	 * @protected
	 */
	protected function _sendMessage($message) {
		$response = $this->ubrirLibHttp->post($message);
		
		if ($response === false) {
			return $this->ubrirLibHttp->error;
		}
		
		$http = $this->ubrirLibHttp->getHttpCode();
		
		if (!in_array($http['code'], array('200', '301', '302'))) {
			return $http['message'];
		}
		
		return $response;
	}
	
	
	/**
	 * 
	 * define all headers here
	 * @private
	 */
	private function _getHeaders() {
		$header = array();
		$header[] = 'Authorization: Basic '.base64_encode($this->_userId.':'.$this->_apiKey);
		$header[] = 'Content-Type: application/xml; charset=UTF-8';
		$header[] = 'Accept: application/xml; charset=UTF-8';
		$header[] = 'X-Powered-By: PHP/'.phpversion();
		return $header;
	}
	
	
	/**
	 * 
	 * prepare $this->errors for insertion of errors
	 * @private
	 */
	private function _createErrorArrayStructure() {
		if (!isset($this->errors[$this->errorPos])) {
			$this->errors[$this->errorPos] = array();
		}
		
		if (!isset($this->errors[$this->errorPos][$this->errorCountTemp])) {
			$this->errors[$this->errorPos][$this->errorCountTemp] = array();
		}
	}
	
	
	/**
	 * 
	 * prepare $this->warnings for insertion of errors
	 * @see _createErrorsarrayStructure();
	 * @private
	 */
	private function _createWarningArrayStructure() {
		if (!isset($this->warnings[$this->errorPos])) {
			$this->warnings[$this->errorPos] = array();
		}
		
		if (!isset($this->warnings[$this->errorPos][$this->errorCountTemp])) {
			$this->warnings[$this->errorPos][$this->errorCountTemp] = array();
		}
	}
	
	
	/**
	 * display stacktrace
	 * @private
	 * @param $provideObject
	 */
	private function _backtrace($provideObject = false) {
		$last = '';
		$file = __FILE__;
		$args = '';
		$message = '';
		
		foreach (debug_backtrace($provideObject) as $row) {
			if ($last != $row['file']) {
				$message .= "File: $file<br>\n";
			}
			
			$last=$row['file'];
			$message .= ' Line: $row[line]: ';
			
			if ($row['class']!='') {
				$message .= '$row[class]$row[type]$row[function]';
			} else {
				$message .= '$row[function]';
			}
			
			$message .= '(';
			$message .= join('', '',$args);
			$message .= ")<br>\n";
		}
		
		return $message;
	}
	
	
	/**
	 *
	 *Setter for an error
	 * @public
	 * @param string $message
	 * @param boolean $fatal
	 */
	public function error($message, $fatal = false){
		$errorArray = array('message' => 'Error: '.$message, 'code' => '10');
		$this->errors['global'][] = $errorArray;
	}
	
	
	/**
	 * 
	 * error while parsing xml
	 * @public
	 * @param unknown_type $message
	 */
	public function fatalError($message){
		return $this->error($message, true);
	}
	
	
	/**
	 * 
	 * Enable logging in object
	 * @see ubrirLib setLogEnabled
	 * @deprecated
	 * @public
	 */
	public function enableLog() {
		$this->enableLogging = true;
		return $this;
	}
	
	
	/**
	 * 
	 * Disable logging in object
	 * @see ubrirLib setLogDisabled
	 * @deprecated
	 * @public
	 */
	public function disableLog() {
		$this->enableLogging = false;
		return $this;
	}
	
	
	/**
	 * 
	 * Set logging enable
	 * @uses enableLog();
	 * @public
	 */
	public function setLogEnabled() {
		$this->enableLogging = true;
		return $this;
	}
	
	
	/**
	 * 
	 * Set logging disabled
	 * @uses disableLog();
	 * @public
	 */
	public function setLogDisabled() {
		$this->enableLogging = false;
		return $this;
	}
	
	
	/**
	 * 
	 * Set the ubrirLibLogger object
	 * @param object $ubrirLibLogger
	 * @public
	 */
	public function setLogger($ubrirLibLogger) {
		$this->ubrirLibLogger = $ubrirLibLogger;
	}
	
	
	/**
	 * 
	 * log the given string into warning_log.txt
	 * use $this->enableLog(); to enable logging before!
	 * @param string $message
	 */
	public function logWarning($message) {
		if ($this->enableLogging) {
			$uri = dirname(__FILE__).'/logs/warning_log.txt';
			$this->ubrirLibLogger->log($message, $uri);
		}
	}
	
	
	/**
	 * 
	 * log the given string into error_log.txt
	 * use $this->enableLog(); to enable logging before!
	 * @param string $message
	 */
	public function logError($message) {
		if ($this->enableLogging) {
			$uri = dirname(__FILE__).'/logs/error_log.txt';
			$this->ubrirLibLogger->log($message, $uri);
		}
	}
	
	
	/**
	 * 
	 * log the given string into log.txt
	 * use $this->enableLog(); to enable logging before!
	 * @param string $message
	 */
	public function log($message) {
		if ($this->enableLogging) {
			$uri = $this->_logfilePath ? $this->_logfilePath : dirname(__FILE__).'/logs/log.xml';
			$this->ubrirLibLogger->log($message, $uri);
		}
	}
	
	
	/**
	 * 
	 * Set the path of the logfile
	 * @param string $path
	 */
	public function setLogfilePath($path) {
		$this->_logfilePath = $path;
	}
	
	
	/**
	 * sets the api version which should be used
	 * @param float $version
	 */
	public function setApiVersion($version) {
		$this->_apiVersion = $version;
	}
	
	
	/**
	 *
	 * Handle Errors occurred
	 */
	protected function _handleErrors() {
		//handle errors
		if (isset($this->_response['errors']['error'])) {
			if (!isset($this->_response['errors']['error'][0])) {
				$tmp = $this->_response['errors']['error'];
				unset($this->_response['errors']['error']);
				$this->_response['errors']['error'][0] = $tmp;
			}
			
			foreach ($this->_response['errors']['error'] as $error) {
				$this->errors['global'][] = $this->_getErrorBlock($error);
			}
		}
		
		foreach ($this->_products as $product) {
			if (isset($this->_response['errors'][$product])) {
				if (!isset($this->_response['errors'][$product]['errors']['error'][0])) {
					$tmp = $this->_response['errors'][$product]['errors']['error'];
					unset($this->_response['errors'][$product]['errors']['error']);
					$this->_response['errors'][$product]['errors']['error'][0] = $tmp;
				}
				
				foreach ($this->_response['errors'][$product]['errors']['error'] as $error) {
					$this->errors[$product][] = $this->_getErrorBlock($error);
				}
			}
		}
		
		//handle warnings
		if (isset($this->_response['new_transaction']['warnings']['warning'])) {
			if (!isset($this->_response['new_transaction']['warnings']['warning'][0])) {
				$tmp = $this->_response['new_transaction']['warnings']['warning'];
				unset($this->_response['new_transaction']['warnings']['warning']);
				$this->_response['new_transaction']['warnings']['warning'][0] = $tmp;
			}
			
			foreach ($this->_response['new_transaction']['warnings']['warning'] as $warning) {
				$this->warnings['global'][] = $this->_getErrorBlock($warning);
			}
		}
		
		foreach ($this->_products as $product) {
			if (isset($this->_response['new_transaction']['warnings'][$product])) {
				if (!isset($this->_response['new_transaction']['warnings'][$product]['warnings']['warning'][0])) {
					$tmp = $this->_response['new_transaction']['warnings'][$product]['warnings']['warning'];
					unset($this->_response['new_transaction']['warnings'][$product]['warnings']['warning']);
					$this->_response['new_transaction']['warnings'][$product]['warnings']['warning'][0] = $tmp;
				}
				
				foreach ($this->_response['new_transaction']['warnings'][$product]['warnings']['warning'] as $warning) {
					$this->warnings[$product][] = $this->_getErrorBlock($warning);
				}
			}
		}
	}
	
	
	/**
	 *
	 * parse the XML received or being sent
	 */
	protected function _parseXml() {}
	
	
	/**
	 *
	 * Getter for error block
	 * @param unknown_type $error
	 */
	protected function _getErrorBlock($error) {
		$newError['code'] = isset($error['code']['@data']) ? $error['code']['@data'] : '';
		$newError['message'] = isset($error['message']['@data']) ? $error['message']['@data'] : '';
		$newError['field'] = isset($error['field']['@data']) ? $error['field']['@data'] : '';
		return $newError;
	}
	
	
	/**
	 * 
	 * Static debug method
	 * @param mixed $var
	 * @param boolean $showHtml
	 */
	public static function debug($var = false, $showHtml = false) {
		echo "\n<pre class=\"ubrir-debug\">\n";
		$var = print_r($var, true);
		
		if ($showHtml) {
			$var = str_replace('<', '&lt;', str_replace('>', '&gt;', $var));
		}
		
		echo $var . "\n</pre>\n";
	}
}

/// @endcond
?>