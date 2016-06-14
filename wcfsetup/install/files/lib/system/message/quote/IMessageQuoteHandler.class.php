<?php
namespace wcf\system\message\quote;

/**
 * Default interface for quote handlers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Message\Quote
 */
interface IMessageQuoteHandler {
	/**
	 * Renders a template for given quotes.
	 * 
	 * @param	array		$data
	 * @param	boolean		$supportPaste
	 * @return	string
	 */
	public function render(array $data, $supportPaste = false);
	
	/**
	 * Renders a list of quotes for insertation.
	 * 
	 * @param	mixed[][]	$data
	 * @param	boolean		$render
	 * @return	string[]
	 */
	public function renderQuotes(array $data, $render = true);
}
