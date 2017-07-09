<?php
namespace wcf\data\user\trophy;
use wcf\data\user\UserAction;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Provides user trophy actions. 
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Trophy
 * @since	3.1
 */
class UserTrophyAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.trophy.canAwardTrophy'];
	
	/**
	 * @inheritDoc
	 */
	public function create() {
		$returnValues = parent::create();
		
		(new UserAction([$returnValues->userID], 'update', [
			'counters' => [
				'trophyPoints' => 1
			]
		]))->executeAction();
		
		return $returnValues; 
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		$returnValues = parent::delete();
		
		$updateUserTrophies = [];
		
		/** @var $object UserTrophyEditor */
		foreach ($this->objects as $object) {
			if (!isset($updateUserTrophies[$object->userID])) $updateUserTrophies[$object->userID] = 0; 
			$updateUserTrophies[$object->userID]--;
		}
		
		foreach ($updateUserTrophies as $userID => $count) {
			(new UserAction([$userID], 'update', [
				'counters' => [
					'trophies' => $count
				]
			]))->executeAction();
		}
		
		return $returnValues;
	}
}
