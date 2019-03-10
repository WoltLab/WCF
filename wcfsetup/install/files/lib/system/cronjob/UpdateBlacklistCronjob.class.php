<?php
namespace wcf\system\cronjob;
use wcf\data\blacklist\entry\BlacklistEntryAction;
use wcf\data\cronjob\Cronjob;

/**
 * Updates the built-in blacklist data.
 * 
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Cronjob
 * @since       5.2
 */
class UpdateBlacklistCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		if (!BLACKLIST_SFS_ENABLE) return;
		
		(new BlacklistEntryAction([], 'import'))->executeAction();
	}
}
