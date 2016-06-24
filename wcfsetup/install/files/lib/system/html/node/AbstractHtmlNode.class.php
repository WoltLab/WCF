<?php
namespace wcf\system\html\node;

/**
 * Default implementation for html nodes.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Node
 * @since       3.0
 */
abstract class AbstractHtmlNode implements IHtmlNode {
	/**
	 * tag name used to identify elements consumed by this node
	 * @var string
	 */
	protected $tagName = '';
	
	/**
	 * placeholder for inner content when performing direct html replacement
	 * @var string
	 */
	const PLACEHOLDER = '<!-- META_CODE_INNER_CONTENT -->';
	
	/**
	 * @inheritDoc
	 */
	public function getTagName() {
		return $this->tagName;
	}
	
	/**
	 * @inheritDoc
	 */
	public function replaceTag(array $data) {
		throw new \BadMethodCallException("Method replaceTag() is not supported by ".get_class($this));
	}
}
