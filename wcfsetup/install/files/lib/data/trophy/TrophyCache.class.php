<?php
namespace wcf\data\trophy;
use wcf\system\cache\builder\TrophyCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * Trophy cache management. 
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Trophy
 * @since	3.1
 */
class TrophyCache extends SingletonFactory {
	/**
	 * Contains all trophies.
	 * @var Trophy[]
	 */
	protected $trophies;
	
	/**
	 * Contains all enabled trophies.
	 * @var Trophy[]
	 */
	protected $enabledTrophies;
	
	/**
	 * Contains all trophies sorted by the category. 
	 * @var Trophy[]
	 */
	protected $categorySortedTrophies; 
	
	/**
	 * @inheritDoc
	 */
	public function init() {
		$this->trophies = TrophyCacheBuilder::getInstance()->getData();
		$this->enabledTrophies = TrophyCacheBuilder::getInstance()->getData(['onlyEnabled' => 1]);
	}
	
	/**
	 * Returns the trophy with the given trophyID.
	 * 
	 * @param 	integer		$trophyID
	 * @return	Trophy
	 */
	public function getTrophyByID($trophyID) {
		if (isset($this->trophies[$trophyID])) {
			return $this->trophies[$trophyID]; 
		}
		
		return null; 
	}
	
	/**
	 * Returns the trophy with the given trophyID.
	 * 
	 * @param 	integer[]	$trophyIDs
	 * @return	Trophy[]
	 */
	public function getTrophiesByID(array $trophyIDs) {
		$returnValues = []; 
		
		foreach ($trophyIDs as $trophyID) {
			$returnValues[] = $this->getTrophyByID($trophyID);
		}
		
		return $returnValues; 
	}
	
	/**
	 * Returns all trophies for a specific category. 
	 * 
	 * @param 	integer		$categoryID
	 * @return	Trophy[]
	 */
	public function getTrophiesByCategoryID($categoryID) {
		if (!is_array($this->categorySortedTrophies)) {
			$this->categorySortedTrophies = []; 
			
			foreach ($this->trophies as $trophy) {
				if (!isset($this->categorySortedTrophies[$trophy->categoryID])) {
					$this->categorySortedTrophies[$trophy->categoryID] = []; 
				}
				
				$this->categorySortedTrophies[$trophy->categoryID][$trophy->getObjectID()] = $trophy; 
			}
		}
		
		if (!isset($this->categorySortedTrophies[$categoryID])) {
			return []; 
		}
		
		return $this->categorySortedTrophies[$categoryID];
	}
	
	/**
	 * Returns all enabled trophies for a specific category.
	 *
	 * @param 	integer		$categoryID
	 * @return	Trophy[]
	 */
	public function getEnabledTrophiesByCategoryID($categoryID) {
		$trophies = $this->getTrophiesByCategoryID($categoryID);
		
		$returnValues = []; 
		foreach ($trophies as $trophy) {
			if (!$trophy->isDisabled) {
				$returnValues[$trophy->getObjectID()] = $trophy; 
			}
		}
		
		return $returnValues;
	}
	
	/**
	 * Return all trophies. 
	 * 
	 * @return	Trophy[]
	 */
	public function getTrophies() {
		return $this->trophies; 
	}
	
	/**
	 * Return all enabled trophies.
	 *
	 * @return	Trophy[]
	 */
	public function getEnabledTrophies() {
		return $this->enabledTrophies;
	}
	
	/**
	 * Resets the cache for the trophies.
	 */
	public function clearCache() {
		TrophyCacheBuilder::getInstance()->reset();
		TrophyCacheBuilder::getInstance()->reset(['onlyEnabled' => 1]);
	}
}
