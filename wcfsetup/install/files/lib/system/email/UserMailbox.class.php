<?php
namespace wcf\system\email;
use wcf\data\user\User;

/**
 * Represents mailbox belonging to a specific registered user.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Email
 * @since	3.0
 */
class UserMailbox extends Mailbox {
	/**
	 * User object belonging to this Mailbox
	 * @var	User
	 */
	protected $user = null;
	
	/**
	 * Creates a new Mailbox.
	 * 
	 * @param	User	$user	User object belonging to this Mailbox
	 */
	public function __construct(User $user) {
		parent::__construct($user->email, $user->username, $user->getLanguage());
		
		$this->user = $user;
	}
	
	/**
	 * Returns the User object belonging to this Mailbox.
	 * 
	 * @return	User
	 */
	public function getUser() {
		return $this->user;
	}
}
