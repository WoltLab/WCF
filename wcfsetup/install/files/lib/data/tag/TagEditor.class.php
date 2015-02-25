<?php
namespace wcf\data\tag;
use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit tags.
 * 
 * @author	Tim Duesterhus, Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.tag
 * @category	Community Framework
 */
class TagEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectEditor::$baseClass
	 */
	protected static $baseClass = 'wcf\data\tag\Tag';
	
	/**
	 * Adds the given tag, and all of it's synonyms as a synonym.
	 * 
	 * @param	\wcf\data\tag\Tag	$synonym
	 */
	public function addSynonym(Tag $synonym) {
		// assign all associations for the synonym with this tag
		$sql = "UPDATE IGNORE	wcf".WCF_N."_tag_to_object
			SET		tagID = ?
			WHERE		tagID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->tagID, $synonym->tagID));
		
		// remove remaining associations (object was tagged with both tags => duplicate key previously ignored)
		$sql = "DELETE FROM	wcf".WCF_N."_tag_to_object
			WHERE		tagID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($synonym->tagID));
		
		$editor = new TagEditor($synonym);
		$editor->update(array(
			'synonymFor' => $this->tagID
		));
		
		$synonymList = new TagList();
		$synonymList->getConditionBuilder()->add('synonymFor = ?', array($synonym->tagID));
		$synonymList->readObjects();
		
		foreach ($synonymList as $synonym) {
			$this->addSynonym($synonym);
		}
	}
}
