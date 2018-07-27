<?php
namespace wcf\data\reaction\type;
use wcf\system\cache\builder\ReactionTypeCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * ReactionType cache management.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Reaction\Type
 * @since	3.2
 */
class ReactionTypeCache extends SingletonFactory {
	/**
	 * Contains all reaction types.
	 * @var ReactionType[]
	 */
	protected $reactionTypes;
	
	/**
	 * Contains all enabled reaction types.
	 * @var ReactionType[]
	 */
	protected $enabledReactionTypes;
	
	/**
	 * @inheritDoc
	 */
	public function init() {
		$this->reactionTypes = ReactionTypeCacheBuilder::getInstance()->getData();
		$this->enabledReactionTypes = ReactionTypeCacheBuilder::getInstance()->getData(['onlyEnabled' => 1]);
	}
	
	/**
	 * Returns the reaction type with the given reactionTypeID.
	 *
	 * @param 	integer		$trophyID
	 * @return	ReactionType
	 */
	public function getReactionTypeByID($trophyID) {
		if (isset($this->reactionTypes[$trophyID])) {
			return $this->reactionTypes[$trophyID];
		}
		
		return null;
	}
	
	/**
	 * Returns the reaction types with the given reactionTypeIDs.
	 *
	 * @param 	integer[]	$reactionTypeIDs
	 * @return	ReactionType[]
	 */
	public function getReactionTypesByID(array $reactionTypeIDs): array {
		$returnValues = [];
		
		foreach ($reactionTypeIDs as $reactionType) {
			$returnValues[] = $this->getReactionTypeByID($reactionType);
		}
		
		return $returnValues;
	}
	
	/**
	 * Return all reaction types.
	 *
	 * @return	ReactionType[]
	 */
	public function getReactionTypes(): array {
		return $this->reactionTypes;
	}
	
	/**
	 * Return all enabled reaction types.
	 *
	 * @return	ReactionType[]
	 */
	public function getEnabledReactionTypes(): array {
		return $this->enabledReactionTypes;
	}
	
	/**
	 * Resets the cache for the trophies.
	 */
	public function clearCache() {
		ReactionTypeCacheBuilder::getInstance()->reset();
		ReactionTypeCacheBuilder::getInstance()->reset(['onlyEnabled' => 1]);
	}
}
