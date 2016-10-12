<?php
namespace wcf\system\like;
use wcf\data\like\ViewableLike;

/**
 * Default interface for viewable like providers.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Like
 */
interface IViewableLikeProvider {
	/**
	 * Prepares a list of likes for output.
	 * 
	 * @param	ViewableLike[]		$likes
	 */
	public function prepare(array $likes);
}
