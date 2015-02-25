<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\WCF;

/**
 * Unbans users and enables disabled avatars and disabled signatures.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category	Community Framework
 */
class UserBanCronjob extends AbstractCronjob {
	/**
	 * @see	\wcf\system\cronjob\ICronjob::execute()
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		// unban users
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	banned = ?,
				banExpires = ?
			WHERE	banned = ?
				AND banExpires <> ?
				AND banExpires <= ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			0,
			0,
			1,
			0,
			TIME_NOW
		));
		
		// enable avatars
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	disableAvatar = ?,
				disableAvatarExpires = ?
			WHERE	disableAvatar = ?
				AND disableAvatarExpires <> ?
				AND disableAvatarExpires <= ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			0,
			0,
			1,
			0,
			TIME_NOW
		));
		
		// enable signatures
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	disableSignature = ?,
				disableSignatureExpires = ?
			WHERE	disableSignature = ?
				AND disableSignatureExpires <> ?
				AND disableSignatureExpires <= ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			0,
			0,
			1,
			0,
			TIME_NOW
		));
	}
}
