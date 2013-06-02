<?php
namespace wcf\system\tagging;
use wcf\system\cache\builder\TagCloudCacheBuilder;
use wcf\system\language\LanguageFactory;

/**
 * This class holds a list of tags that can be used for creating a tag cloud.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.tagging
 * @subpackage	system.tagging
 * @category	Community Framework
 */
class TagCloud {
	/**
	 * max font size
	 * @var	integer
	 */
	const MAX_FONT_SIZE = 170;
	
	/**
	 * min font size
	 * @var	integer
	 */
	const MIN_FONT_SIZE = 85;
	
	/**
	 * list of tags
	 * @var	array<wcf\data\tag\TagCloudTag>
	 */
	protected $tags = array();
	
	/**
	 * max value of tag counter
	 * @var	integer
	 */
	protected $maxCounter = 0;
	
	/**
	 * min value of tag counter
	 * @var	integer
	 */
	protected $minCounter = 4294967295;
	
	/**
	 * active language ids
	 * @var	array<integer>
	 */
	protected $languageIDs = array();
	
	/**
	 * Contructs a new TagCloud object.
	 *
	 * @param	array<integer>	$languageIDs
	 */
	public function __construct(array $languageIDs = array()) {
		$this->languageIDs = $languageIDs;
		if (empty($this->languageIDs)) {
			$this->languageIDs = array_keys(LanguageFactory::getInstance()->getLanguages());
		}
		
		// init cache
		$this->loadCache();
	}
	
	/**
	 * Loads the tag cloud cache.
	 */
	protected function loadCache() {
		$this->tags = TagCloudCacheBuilder::getInstance()->getData($this->languageIDs);
	}
	
	/**
	 * Gets a list of weighted tags.
	 * 
	 * @param	integer				$slice
	 * @return	array<wcf\data\tag\TagCloudTag>	the tags to get
	 */
	public function getTags($slice = 50) {
		// slice list
		$tags = array_slice($this->tags, 0, min($slice, count($this->tags)));
		
		// get min / max counter
		foreach ($tags as $tag) {
			if ($tag->counter > $this->maxCounter) $this->maxCounter = $tag->counter;
			if ($tag->counter < $this->minCounter) $this->minCounter = $tag->counter;
		}
		
		// assign sizes
		foreach ($tags as $tag) {
			$tag->setSize($this->calculateSize($tag->counter));
		}
		
		// sort alphabetically
		ksort($tags);
		
		// return tags
		return $tags;
	}
	
	/**
	 * Returns the size of a tag with given number of uses for a weighted list.
	 * 
	 * @param	integer		$counter
	 * @return	double
	 */
	private function calculateSize($counter) {
		if ($this->maxCounter == $this->minCounter) {
			return 100;
		}
		else {
			return (self::MAX_FONT_SIZE - self::MIN_FONT_SIZE) / ($this->maxCounter - $this->minCounter) * $counter + self::MIN_FONT_SIZE - ((self::MAX_FONT_SIZE - self::MIN_FONT_SIZE) / ($this->maxCounter - $this->minCounter)) * $this->minCounter;
		}
	}
}
