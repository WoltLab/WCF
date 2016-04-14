<?php
namespace wcf\system\cronjob;
use wcf\data\acp\session\virtual\ACPSessionVirtualEditor;
use wcf\data\acp\session\ACPSessionEditor;
use wcf\data\cronjob\Cronjob;
use wcf\data\session\virtual\SessionVirtualEditor;
use wcf\data\session\SessionEditor;

/**
 * Deletes expired sesions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category	Community Framework
 */
class SessionCleanUpCronjob extends AbstractCronjob {
	/**
	 * @see	\wcf\system\cronjob\ICronjob::execute()
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		ACPSessionEditor::deleteExpiredSessions(TIME_NOW - SESSION_TIMEOUT);
		ACPSessionVirtualEditor::deleteExpiredSessions(TIME_NOW - SESSION_TIMEOUT);
		SessionEditor::deleteExpiredSessions(TIME_NOW - SESSION_TIMEOUT);
		SessionVirtualEditor::deleteExpiredSessions(TIME_NOW - SESSION_TIMEOUT);
	}
}
