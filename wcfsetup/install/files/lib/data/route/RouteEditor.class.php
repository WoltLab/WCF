<?php
namespace wcf\data\route;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit routes.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.route
 * @category 	Community Framework
 */
class RouteEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\route\Route';
}
