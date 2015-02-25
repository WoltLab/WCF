<?php
namespace wcf\system\cache\builder;

/**
 * Caches user options and categories
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class UserOptionCacheBuilder extends OptionCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\OptionCacheBuilder::$optionClassName
	 */
	protected $optionClassName = 'wcf\data\user\option\UserOption';
	
	/**
	 * @see	\wcf\system\cache\builder\OptionCacheBuilder::$tableName
	 */
	protected $tableName = 'user_option';
}
