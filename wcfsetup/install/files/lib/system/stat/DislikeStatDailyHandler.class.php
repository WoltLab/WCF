<?php
namespace wcf\system\stat;
use wcf\data\like\Like;

/**
 * Stat handler implementation for dislike stats.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.stat
 * @category	Community Framework
 */
class DislikeStatDailyHandler extends LikeStatDailyHandler {
	protected $likeValue = Like::DISLIKE;
}
