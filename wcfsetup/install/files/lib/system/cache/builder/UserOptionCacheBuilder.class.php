<?php
namespace wcf\system\cache\builder;
use wcf\data\user\option\UserOption;

/**
 * Caches user options and categories
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class UserOptionCacheBuilder extends OptionCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected $optionClassName = UserOption::class;
	
	/**
	 * @inheritDoc
	 */
	protected $tableName = 'user_option';
}
