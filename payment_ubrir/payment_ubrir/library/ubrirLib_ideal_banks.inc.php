<?php
defined('_JEXEC') or die('Restricted access');
require_once dirname(__FILE__).'/ubrirLib_abstract.inc.php';

/**
 * This class encapsulates retrieval of listed banks of the Netherlands
 *
 * Copyright (c) 2012 ubrir AG
 * 
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 *
 * $Date: 2013-02-27 11:37:15 +0100 (Wed, 27 Feb 2013) $
 * @version ubrirLib 1.5.4  $Id: ubrirLib_ideal_banks.inc.php 6029 2013-02-27 10:37:15Z rotsch $
 * @author ubrir AG http://www.ubrir.com (integration@ubrir.com)
 *
 */
class ubrirLib_iDeal_Banks extends ubrirLib_Abstract {
	
	protected $_xmlRootTag = 'ideal';
	
	protected $_parameters = array();
	
	protected $_response = array();
	
	private $_banks = array();
	
	
	/**
	 * 
	 * Constructor for ubrirLib_iDeal_Banks
	 * @param string $configKey
	 * @param strign $apiUrl
	 */
	public function __construct($configKey, $apiUrl = '') {
		list ($userId, $projectId, $apiKey) = explode(':', $configKey);
		parent::__construct($userId, $apiKey, $apiUrl.'/banks');
	}
	
	
	/**
	 * 
	 * Getter for bank list
	 */
	public function getBanks() {
		return $this->_banks;
	}
	
	
	/**
	 * Parse the xml (override)
	 * (non-PHPdoc)
	 * @see ubrirLib_Abstract::_parseXml()
	 */
	protected function _parseXml() {
		if (isset($this->_response['ideal']['banks']['bank'][0]['code']['@data'])) {
			foreach($this->_response['ideal']['banks']['bank'] as $key => $bank) {
				$this->_banks[$key]['code'] = $bank['code']['@data'];
				$this->_banks[$key]['name'] = $bank['name']['@data'];
			}
		}
	}
}
?>