<?php
namespace wcf\system\html;

/**
 * Default interface for html processors.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html
 * @since       3.0
 */
interface IHtmlProcessor {
	/**
	 * Returns the parsed html.
	 * 
	 * @return      string          parsed html
	 */
	public function getHtml();
}
