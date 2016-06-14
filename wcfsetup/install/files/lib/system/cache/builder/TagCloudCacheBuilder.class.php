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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class TagCloudCacheBuilder extends AbstractCacheBuilder {
	/**
	 * list of tags
	 * @var	TagCloudTag[]
	 */
	protected $tags = [];
	
	/**
	 * language ids
	 * @var	integer
	 */
	protected $languageIDs = [];
	
	/**
	 * @inheritDoc
	 */
	protected $maxLifetime = 3600;
	
	/**
	 * object type ids
	 * @var	integer
	 */
	protected $objectTypeIDs = [];
	
	/**
	 * @inheritDoc
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
	 * @param	integer[]		$parameters
	 * @return	integer[]
	 */
	protected function parseLanguageIDs(array $parameters) {
		// handle special '0' value
		if (in_array(0, $parameters)) {
			// discard all language ids
			$parameters = [];
		}
		
		return $parameters;
	}
	
	/**
	 * Reads associated tags.
	 */
	protected function getTags() {
		$this->tags = [];
		
		if (!empty($this->objectTypeIDs)) {
			// get tag ids
			$tagIDs = [];
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('object.objectTypeID IN (?)', [$this->objectTypeIDs]);
			$conditionBuilder->add('object.languageID IN (?)', [$this->languageIDs]);
			$sql = "SELECT		COUNT(*) AS counter, object.tagID
				FROM		wcf".WCF_N."_tag_to_object object
				".$conditionBuilder."
				GROUP BY	object.tagID
				ORDER BY	counter DESC";
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
				uasort($this->tags, ['self', 'compareTags']);
			}
		}
	}
	
	/**
	 * Compares the weight between two tags.
	 * 
	 * @param	\wcf\data\tag\TagCloudTag	$tagA
	 * @param	\wcf\data\tag\TagCloudTag	$tagB
	 * @return	integer
	 */
	protected static function compareTags($tagA, $tagB) {
		if ($tagA->counter > $tagB->counter) return -1;
		if ($tagA->counter < $tagB->counter) return 1;
		return 0;
	}
}
