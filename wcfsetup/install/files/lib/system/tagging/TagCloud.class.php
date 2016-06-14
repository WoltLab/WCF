<?php
namespace wcf\system\tagging;
use wcf\data\tag\TagCloudTag;
use wcf\system\cache\builder\TagCloudCacheBuilder;
use wcf\system\language\LanguageFactory;

/**
 * This class holds a list of tags that can be used for creating a tag cloud.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Tagging
 */
class TagCloud {
	/**
	 * max font size
	 * @var	integer
	 * @deprecated 3.0
	 */
	const MAX_FONT_SIZE = 170;
	
	/**
	 * min font size
	 * @var	integer
	 * @deprecated 3.0
	 */
	const MIN_FONT_SIZE = 85;
	
	/**
	 * list of tags
	 * @var	TagCloudTag[]
	 */
	protected $tags = [];
	
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
	 * @var	integer[]
	 */
	protected $languageIDs = [];
	
	/**
	 * Contructs a new TagCloud object.
	 * 
	 * @param	integer[]	$languageIDs
	 */
	public function __construct(array $languageIDs = []) {
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
	 * @param	integer		$slice
	 * @return	TagCloudTag[]	the tags to get
	 */
	public function getTags($slice = 50) {
		// slice list
		/** @var TagCloudTag[] $tags */
		$tags = array_slice($this->tags, 0, min($slice, count($this->tags)));
		
		// get min / max counter
		foreach ($tags as $tag) {
			if ($tag->counter > $this->maxCounter) $this->maxCounter = $tag->counter;
			if ($tag->counter < $this->minCounter) $this->minCounter = $tag->counter;
		}
		
		// assign sizes
		foreach ($tags as $tag) {
			$tag->setWeight($this->calculateWeight($tag->counter));
		}
		
		// sort alphabetically
		ksort($tags);
		
		// return tags
		return $tags;
	}
	
	private function calculateWeight($counter) {
		if ($this->maxCounter == $this->minCounter) {
			return 2;
		}
		else {
			$weight = round(log($counter) / log($this->maxCounter) * 7);
			if ($weight < 1) $weight = 1;
			return $weight;
		}
	}
}
