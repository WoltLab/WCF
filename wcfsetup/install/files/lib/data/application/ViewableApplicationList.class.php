<?php
namespace wcf\data\application;

/**
 * Represents a list of viewable applications.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.application
 * @category	Community Framework
 */
class ViewableApplicationList extends ApplicationList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$decoratorClassName
	 */
	public $decoratorClassName = ViewableApplication::class;
}
