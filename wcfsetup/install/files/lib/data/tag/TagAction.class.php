<?php
namespace wcf\data\tag;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISearchAction;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes tagging-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Tag
 * 
 * @method	Tag		create()
 * @method	TagEditor[]	getObjects()
 * @method	TagEditor	getSingleObject()
 */
class TagAction extends AbstractDatabaseObjectAction implements ISearchAction {
	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = ['getSearchResultList'];
	
	/**
	 * @inheritDoc
	 */
	protected $className = TagEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.content.tag.canManageTag'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.content.tag.canManageTag'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['delete', 'update'];
	
	/**
	 * tag for which other tags will be used as synonyms
	 * @var	\wcf\data\tag\TagEditor
	 */
	public $tagEditor = null;
	
	/**
	 * @inheritDoc
	 */
	public function validateGetSearchResultList() {
		$this->readString('searchString', false, 'data');
		
		if (isset($this->parameters['data']['excludedSearchValues']) && !is_array($this->parameters['data']['excludedSearchValues'])) {
			throw new UserInputException('excludedSearchValues');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSearchResultList() {
		$excludedSearchValues = [];
		if (isset($this->parameters['data']['excludedSearchValues'])) {
			$excludedSearchValues = $this->parameters['data']['excludedSearchValues'];
		}
		$list = [];
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add("name LIKE ?", [$this->parameters['data']['searchString'].'%']);
		if (!empty($excludedSearchValues)) {
			$conditionBuilder->add("name NOT IN (?)", [$excludedSearchValues]);
		}
		
		// find tags
		$sql = "SELECT	tagID, name
			FROM	wcf".WCF_N."_tag
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql, 5);
		$statement->execute($conditionBuilder->getParameters());
		while ($row = $statement->fetchArray()) {
			$list[] = [
				'label' => $row['name'],
				'objectID' => $row['tagID']
			];
		}
		
		return $list;
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		$returnValue = parent::delete();
		
		$this->unmarkItems();
		
		return $returnValue;
	}
	
	/**
	 * Validates the 'setAsSynonyms' action.
	 * 
	 * @since	3.0
	 */
	public function validateSetAsSynonyms() {
		WCF::getSession()->checkPermissions(['admin.content.tag.canManageTag']);
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (count($this->objects) < 2) {
				throw new UserInputException('objectIDs');
			}
		}
		
		$this->readInteger('tagID');
		$this->tagEditor = new TagEditor(new Tag($this->parameters['tagID']));
		if (!$this->tagEditor->tagID) {
			throw new UserInputException('tagID');
		}
	}
	
	/**
	 * Sets a number of tags as a synonyms of another tag.
	 *
	 * @since	3.0
	 */
	public function setAsSynonyms() {
		// the "main" tag may not be a synonym itself
		if ($this->tagEditor->synonymFor) {
			$this->tagEditor->update([
				'synonymFor' => null
			]);
		}
		
		foreach ($this->getObjects() as $tagEditor) {
			$this->tagEditor->addSynonym($tagEditor->getDecoratedObject());
		}
		
		$this->unmarkItems();
	}
	
	/**
	 * Unmarks tags.
	 * 
	 * @param	integer[]		$tagIDs
	 * @since	3.0
	 */
	protected function unmarkItems(array $tagIDs = []) {
		if (empty($tagIDs)) {
			$tagIDs = $this->objectIDs;
		}
		
		if (!empty($tagIDs)) {
			ClipboardHandler::getInstance()->unmark($tagIDs, ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.tag'));
		}
	}
}
