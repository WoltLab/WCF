<?php
namespace wcf\system\bbcode;
use wcf\data\DatabaseObjectDecorator;

/**
 * Provides an abstract implementation for bbcodes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.bbcode
 * @subpackage	system.bbcode
 * @category	Community Framework
 */
abstract class AbstractBBCode extends DatabaseObjectDecorator implements IBBCode {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\bbcode\BBCode';
}
