<?php
declare(strict_types=1);
namespace wcf\system\form\builder;

/**
 * Represents a form node that has a parent node.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder
 * @since	3.2
 */
interface IFormChildNode extends IFormNode {
	/**
	 * Returns the parent node of this node.
	 * 
	 * @return	IFormParentNode			parent node of this node
	 * 
	 * @throws	\BadMethodCallException		if the parent node has not been set previously
	 */
	public function getParent();
	
	/**
	 * Sets the parent node of this node and returns this node.
	 * 
	 * @param	IFormParentNode		$parentNode	new parent node of this node
	 * @return	static					this node
	 *
	 * @throws	\BadMethodCallException			if the parent node has already been set
	 */
	public function parent(IFormParentNode $parentNode);
}
