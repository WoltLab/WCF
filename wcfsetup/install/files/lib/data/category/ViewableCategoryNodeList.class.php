<?php
namespace wcf\data\category;

/**
 * Represents a list of viewable category nodes.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.category
 * @category	Community Framework
 */
class ViewableCategoryNodeList extends CategoryNodeList {
	/**
	 * @see	wcf\data\category\CategoryNodeList::$nodeClassName
	 */
	protected $nodeClassName = 'wcf\data\category\ViewableCategoryNode';
}
