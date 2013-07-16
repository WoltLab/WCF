<?php
namespace wcf\system\user\activity\point;
use wcf\data\object\type\AbstractObjectTypeProcessor;

/**
 * Abstract implementation of a user activity point object processor.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.activity.point
 * @category	Community Framework
 */
abstract class AbstractUserActivityPointObjectProcessor extends AbstractObjectTypeProcessor implements IUserActivityPointObjectProcessor {
	/**
	 * number of objects processed during one request
	 * @var	integer
	 */
	public $limit = 500;
}
