<?php
namespace wcf\data\page;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of pages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page
 * @category	Community Framework
 * @since	2.2
 *
 * @method	Page		current()
 * @method	Page[]		getObjects()
 * @method	Page|null	search($objectID)
 * @property	Page[]		$objects
 */
class PageList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Page::class;
}
