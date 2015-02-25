<?php
namespace wcf\system\message\quote;

/**
 * Default interface for quote handlers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.message.quote
 * @category	Community Framework
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
	 * @param	array<array>	$data
	 * @param	boolean		$render
	 * @return	array<string>
	 */
	public function renderQuotes(array $data, $render = true);
}
