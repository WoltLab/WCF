<?php
namespace wcf\data\reaction\object;
use wcf\data\like\Like;

/**
 * Any reactionable object should implement this interface.
 *
 * @TODO To support backward compatibility, this interface should also be implemented in WSC 3.0.
 * 
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Reaction\Object
 */
interface IReactionableObject {
	/**
	 * Sends a notification for this reaction.
	 *
	 * @param	Like	$like
	 */
	public function sendReactionNotification(Like $like);
}