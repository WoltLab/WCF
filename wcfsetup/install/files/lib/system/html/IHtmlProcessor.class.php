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
	 * Processes the input html string.
	 * 
	 * @param       string          $html           html string
	 * @param       string          $objectType     object type identifier
	 * @param       integer         $objectID       object id
	 */
	public function process($html, $objectType, $objectID);
	
	/**
	 * Returns the parsed html.
	 * 
	 * @return      string          parsed html
	 */
	public function getHtml();
}
