<?php
/**
 * @author ubrir AG (integration@ubrir.com)
 * @link http://www.ubrir.com/
 * 
 * Copyright (c) 2012 ubrir AG
 *
 * Released under the GNU General Public License (Version 2)
 * [http://www.gnu.org/licenses/gpl-2.0.html]
 */

/**
 * 
 * Implementation of simple text
 *
 */
class ubrirText extends ubrirElement {
	
	public $text;
	
	public $escape = false;
	
	
	/**
	 * 
	 * Constructor for ubrirText
	 * @param strng $text
	 * @param boolean $escape
	 * @param boolean $trim
	 */
	public function __construct($text, $escape = false, $trim = true) {
		$this->text = $trim ? trim($text) : $text;
		$this->escape = $escape;
	}
	
	
	/**
	 * Renders the element (override)
	 * (non-PHPdoc)
	 * @see ubrirElement::render()
	 */
	public function render() {
		return $this->escape ? htmlspecialchars($this->text) : $this->text;
	}
}
?>