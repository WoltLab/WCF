<?php
namespace wcf\system\html\input\filter;

/**
 * Default interface for html input filters.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Input\Filter
 * @since       3.0
 */
interface IHtmlInputFilter {
	/**
	 * Applies filters on unsafe html.
	 * 
	 * @param	string		$html		unsafe html
	 * @return	string		filtered html
	 */
	public function apply($html);
}
