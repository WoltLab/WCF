<?php
namespace wcf\system\stat;
use wcf\data\like\Like;

/**
 * Stat handler implementation for dislike stats.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Stat
 */
class DislikeStatDailyHandler extends LikeStatDailyHandler {
	protected $likeValue = Like::DISLIKE;
}
