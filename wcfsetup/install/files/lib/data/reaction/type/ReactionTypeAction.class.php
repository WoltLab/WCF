<?php
namespace wcf\data\reaction\type;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
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
	public function create() {
		/** @var ReactionType $reactionType */
		$reactionType = parent::create();
		$reactionTypeEditor = new ReactionTypeEditor($reactionType);
		
		// i18n
		$updateData = [];
		if (isset($this->parameters['title_i18n'])) {
			I18nHandler::getInstance()->save(
				$this->parameters['title_i18n'],
				'wcf.reactionType.title' . $reactionType->reactionTypeID,
				'wcf.reactionType',
				1
			);
			
			$updateData['title'] = 'wcf.reactionType.title' . $reactionType->reactionTypeID;
		}
		
		if (!empty($updateData)) {
			$reactionTypeEditor->update($updateData);
		}
		
		return $reactionType;
	}
	
	/**
	 * @inheritDoc
	 */
	public function update() {
		parent::update();
		
		// i18n
		foreach ($this->getObjects() as $object) {
			if (isset($this->parameters['title_i18n'])) {
				I18nHandler::getInstance()->save(
					$this->parameters['title_i18n'],
					'wcf.reactionType.title' . $object->reactionTypeID,
					'wcf.reactionType',
					1
				);
				
				$object->update([
					'title' => 'wcf.reactionType.title' . $object->reactionTypeID
				]);
			}
		}
	}
	
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
