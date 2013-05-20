<?php
namespace wcf\system\cache\builder;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\tag\Tag;
use wcf\data\tag\TagCloudTag;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Caches the tag cloud.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.tagging
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class TagCloudCacheBuilder extends AbstractCacheBuilder {
	/**
	 * list of tags
	 * @var	array<wcf\data\tag\TagCloudTag>
	 */
	protected $tags = array();
	
	/**
	 * language ids
	 * @var	integer
	 */
	protected $languageIDs = array();
	
	/**
	 * @see	wcf\system\cache\builder\AbstractCacheBuilder::$maxLifetime
	 */
	protected $maxLifetime = 3600;
	
	/**
	 * object type ids
	 * @var	integer
	 */
	protected $objectTypeIDs = array();
	
	/**
	 * @see	wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	protected function rebuild(array $parameters) {
		$this->languageIDs = $this->parseLanguageIDs($parameters);
		
		// get all taggable types
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.tagging.taggableObject');
		foreach ($objectTypes as $objectType) {
			$this->objectTypeIDs[] = $objectType->objectTypeID;
		}
		
		// get tags
		$this->getTags();
		
		return $this->tags;
	}
	
	/**
	 * Parses a list of language ids. If one given language id evaluates to '0' all ids will be discarded.
	 * 
	 * @param	array<integer>		$parameters
	 * @return	array<integer>
	 */
	protected function parseLanguageIDs(array $parameters) {
		// handle special '0' value
		if (in_array(0, $parameters)) {
			// discard all language ids
			$parameters = array();
		}
		
		return $parameters;
	}
	
	/**
	 * Reads associated tags.
	 */
	protected function getTags() {
		if (!empty($this->objectTypeIDs)) {
			// get tag ids
			$tagIDs = array();
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('object.objectTypeID IN (?)', array($this->objectTypeIDs));
			$conditionBuilder->add('object.languageID IN (?)', array($this->languageIDs));
			$sql = "SELECT		COUNT(*) AS counter, object.tagID
				FROM 		wcf".WCF_N."_tag_to_object object
				".$conditionBuilder->__toString()."
				GROUP BY 	object.tagID
				ORDER BY 	counter DESC";
			$statement = WCF::getDB()->prepareStatement($sql, 500);
			$statement->execute($conditionBuilder->getParameters());
			while ($row = $statement->fetchArray()) {
				$tagIDs[$row['tagID']] = $row['counter'];
			}
			
			// get tags
			if (!empty($tagIDs)) {
				$sql = "SELECT	*
					FROM	wcf".WCF_N."_tag
					WHERE	tagID IN (?".(count($tagIDs) > 1 ? str_repeat(',?', count($tagIDs) - 1) : '').")";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array_keys($tagIDs));
				while ($row = $statement->fetchArray()) {
					$row['counter'] = $tagIDs[$row['tagID']];
					$this->tags[$row['name']] = new TagCloudTag(new Tag(null, $row));
				}
				
				// sort by counter
				uasort($this->tags, array('self', 'compareTags'));
			}
		}
	}
	
	/**
	 * Compares the weight between two tags.
	 * 
	 * @param	wcf\data\tag\TagCloudTag	$tagA
	 * @param	wcf\data\tag\TagCloudTag	$tagB
	 * @return	integer
	 */
	protected static function compareTags($tagA, $tagB) {
		if ($tagA->counter > $tagB->counter) return -1;
		if ($tagA->counter < $tagB->counter) return 1;
		return 0;
	}
}
