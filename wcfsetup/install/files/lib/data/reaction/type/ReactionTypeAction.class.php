<?php
namespace wcf\data\reaction\type;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * ReactionType related actions.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Reaction\Type
 * @since	3.2
 *
 * @method	ReactionTypeEditor[]		getObjects()
 * @method	ReactionTypeEditor		getSingleObject()
 */
class ReactionTypeAction extends AbstractDatabaseObjectAction implements ISortableAction {
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.content.reaction.canManageReactionType'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.content.reaction.canManageReactionType'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['delete', 'update', 'updatePosition'];
	
	/**
	 * @inheritDoc
	 */
	public function validateUpdatePosition() {
		// validate permissions
		if (is_array($this->permissionsUpdate) && count($this->permissionsUpdate)) {
			WCF::getSession()->checkPermissions($this->permissionsUpdate);
		}
		else {
			throw new PermissionDeniedException();
		}
		
		if (!isset($this->parameters['data']['structure'])) {
			throw new UserInputException('structure');
		}
		
		$this->readInteger('offset', true, 'data');
	}
	
	/**
	 * @inheritDoc
	 */
	public function updatePosition() {
		$reactionTypeList = new ReactionTypeList();
		$reactionTypeList->readObjects();
		
		$i = $this->parameters['data']['offset'];
		WCF::getDB()->beginTransaction();
		foreach ($this->parameters['data']['structure'][0] as $reactionTypeID) {
			$reactionType = $reactionTypeList->search($reactionTypeID);
			if ($reactionType === null) continue;
			
			$editor = new ReactionTypeEditor($reactionType);
			$editor->update(['showOrder' => $i++]);
		}
		WCF::getDB()->commitTransaction();
	}
}
