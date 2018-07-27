<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\trophy\condition\TrophyConditionHandler;

/**
 * Assigns automatically trophies.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cronjob
 * @since	3.1
 */
class AssignTrophiesCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		if (MODULE_TROPHY) {
			TrophyConditionHandler::getInstance()->assignTrophies(100);
		}
	}
}
