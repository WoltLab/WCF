<?php
namespace wcf\system\html\node;

/**
 * Default interface for html nodes.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Node
 * @since       3.0
 */
interface IHtmlNode {
	/**
	 * Returns the tag name of elements consumed by this node.
	 *
	 * @return      string          tag name of consumed elements
	 */
	public function getTagName();
	
	/**
	 * Processes the provided elements and marks them for replacement if applicable.
	 * 
	 * @param       \DOMElement[]           $elements               static list of matched elements, does not change when removing elements
	 * @param       AbstractHtmlNodeProcessor       $htmlNodeProcessor      node processor instance
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor);
	
	/**
	 * Replaces a placeholder tag with the provided data.
	 *
	 * @param       array   $data   replacement data
	 */
	public function replaceTag(array $data);
}
