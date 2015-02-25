<?php
namespace wcf\system\like;

/**
 * Default interface for viewable like providers.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.like
 * @category	Community Framework
 */
interface IViewableLikeProvider {
	/**
	 * Prepares a list of likes for output.
	 * 
	 * @param	array<\wcf\data\like\ViewableLike>	$likes
	 */
	public function prepare(array $likes);
}
