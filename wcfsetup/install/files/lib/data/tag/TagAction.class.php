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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.tag
 * @category	Community Framework
 */
class TagAction extends AbstractDatabaseObjectAction implements ISearchAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction
	 */
	protected $allowGuestAccess = array('getSearchResultList');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\tag\TagEditor';
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.content.tag.canManageTag');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.content.tag.canManageTag');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('delete', 'update');
	
	/**
	 * tag for which other tags will be used as synonyms
	 * @var	\wcf\data\tag\TagEditor
	 */
	public $tagEditor = null;
	
	/**
	 * @see	\wcf\data\ISearchAction::validateGetSearchResultList()
	 */
	public function validateGetSearchResultList() {
		$this->readString('searchString', false, 'data');
		
		if (isset($this->parameters['data']['excludedSearchValues']) && !is_array($this->parameters['data']['excludedSearchValues'])) {
			throw new UserInputException('excludedSearchValues');
		}
	}
	
	/**
	 * @see	\wcf\data\ISearchAction::getSearchResultList()
	 */
	public function getSearchResultList() {
		$excludedSearchValues = array();
		if (isset($this->parameters['data']['excludedSearchValues'])) {
			$excludedSearchValues = $this->parameters['data']['excludedSearchValues'];
		}
		$list = array();
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add("name LIKE ?", array($this->parameters['data']['searchString'].'%'));
		if (!empty($excludedSearchValues)) {
			$conditionBuilder->add("name NOT IN (?)", array($excludedSearchValues));
		}
		
		// find tags
		$sql = "SELECT	tagID, name
			FROM	wcf".WCF_N."_tag
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql, 5);
		$statement->execute($conditionBuilder->getParameters());
		while ($row = $statement->fetchArray()) {
			$list[] = array(
				'label' => $row['name'],
				'objectID' => $row['tagID']
			);
		}
		
		return $list;
	}
	
	/**
	 * @see	\wcf\data\IDeleteAction::delete()
	 */
	public function delete() {
		$returnValue = parent::delete();
		
		$this->unmarkItems();
		
		return $returnValue;
	}
	
	/**
	 * Validates the 'setAsSynonyms' action.
	 * 
	 * @since	2.2
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
	 * @since	2.2
	 */
	public function setAsSynonyms() {
		// the "main" tag may not be a synonym itself
		if ($this->tagEditor->synonymFor) {
			$this->tagEditor->update([
				'synonymFor' => null
			]);
		}
		
		foreach ($this->objects as $tagEditor) {
			$this->tagEditor->addSynonym($tagEditor->getDecoratedObject());
		}
		
		$this->unmarkItems();
	}
	
	/**
	 * Unmarks tags.
	 * 
	 * @param	integer[]		$tagIDs
	 * @since	2.2
	 */
	protected function unmarkItems(array $tagIDs = array()) {
		if (empty($tagIDs)) {
			$tagIDs = $this->objectIDs;
		}
		
		if (!empty($tagIDs)) {
			ClipboardHandler::getInstance()->unmark($tagIDs, ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.tag'));
		}
	}
}
