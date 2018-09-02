<?php
namespace wcf\data\reaction\type;
use wcf\data\DatabaseObject;
use wcf\data\ITitledObject;
use wcf\system\WCF;

/**
 * Represents a reaction type.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Reaction\Type
 * @since       3.2
 *
 * @property-read	integer		$reactionTypeID		unique id of the reaction type
 * @property-read	string		$title
 * @property-read	integer		$type   		type of the reaction (1 is positive, 0 is neutral and -1 is negative)
 * @property-read	integer		$showOrder		position of the reaction type in relation to the other reaction types
 * @property-read	string		$iconFile		the file location of the icon
 * @property-read       integer		$isDisabled		is `1` if the reaction type is disabled and thus not shown, otherwise `0`
 */
class ReactionType extends DatabaseObject implements ITitledObject {
	/**
	 * The type value, if this reaction type is a positive reaction.
	 * @var	integer
	 */
	const REACTION_TYPE_POSITIVE = 1;
	
	/**
	 * The type value, if this reaction type is a neutral reaction.
	 * @var	integer
	 */
	const REACTION_TYPE_NEUTRAL = 0;
	
	/**
	 * The type value, if this reaction type is a negative reaction.
	 * @var	integer
	 */
	const REACTION_TYPE_NEGATIVE = -1;
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'reactionTypeID';
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get($this->title);
	}
	
	/**
	 * Renders the reaction icon.
	 * 
	 * @return	string
	 */
	public function renderIcon() {
		return WCF::getTPL()->fetch('reactionTypeImage', 'wcf', [
			'reactionType' => $this
		], true);
	}
	
	/**
	 * Returns the url to the icon for this reaction. 
	 * 
	 * @return string
	 */
	public function getIconPath() {
		return WCF::getPath() . 'images/reaction/'. $this->iconFile;
	}
	
	/**
	 * Returns true, if reaction is a positive reaction.
	 * 
	 * @return	bool
	 */
	public function isPositive() {
		return $this->type == self::REACTION_TYPE_POSITIVE;
	}
	
	/**
	 * Returns true, if reaction is a negative reaction.
	 *
	 * @return	bool
	 */
	public function isNegative() {
		return $this->type == self::REACTION_TYPE_NEGATIVE;
	}
	
	/**
	 * Returns true, if reaction is a neutral reaction.
	 *
	 * @return	bool
	 */
	public function isNeutral() {
		return $this->type == self::REACTION_TYPE_NEUTRAL;
	}
}
