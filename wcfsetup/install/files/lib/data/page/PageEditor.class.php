<?php
namespace wcf\data\page;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit pages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page
 * @category	Community Framework
 */
class PageEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = Page::class;
}
