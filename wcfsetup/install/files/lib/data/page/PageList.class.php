<?php
namespace wcf\data\page;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of pages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page
 * @category	Community Framework
 */
class PageList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = 'wcf\data\page\Page';
}
