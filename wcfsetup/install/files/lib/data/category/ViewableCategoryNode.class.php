<?php
namespace wcf\data\category;

/**
 * Represents a viewable category node.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.category
 * @category	Community Framework
 */
class ViewableCategoryNode extends CategoryNode {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::__construct()
	 */
	public function __construct(DatabaseObject $object, $includeDisabledCategories = false, array $excludedCategoryIDs = array()) {
		parent::__construct(new ViewableCategory($object), $includeDisabledCategories, $excludedCategoryIDs);
	}
}
