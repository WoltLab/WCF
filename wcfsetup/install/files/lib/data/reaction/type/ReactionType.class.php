<?php
declare(strict_types=1);
namespace wcf\data\reaction\type;
use wcf\data\DatabaseObject;

/**
 * Represents an object type definition.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Reaction\Type
 * @since       3.2
 *
 * @property-read	integer		$reactionTypeID		unique id of the reaction type
 * @property-read	string		$reactionTitle
 * @property-read	integer		$type   		type of the reaction (1 is positive, 2 is neutral and 3 is negative)
 * @property-read	integer		$showOrder		position of the reaction type in relation to the other reaction types
 * @property-read	integer		$iconType		the icon type of the reaction
 * @property-read	string		$iconFile		the file location of the icon
 * @property-read	string		$iconName		the icon name
 * @property-read	string		$iconColor              the icon color
 * @property-read       integer		$isDisabled	        is `1` if the ad is disabled and thus not shown, otherwise `0`
 */
class ReactionType extends DatabaseObject {
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
	 * The iconType value, if this reaction type is an image.
	 * @var	integer
	 */
	const ICON_TYPE_IMAGE = 1;
	
	/**
	 * The iconType value, if this reaction type is a font icon.
	 * @var	integer
	 */
	const ICON_TYPE_ICON = 2;
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get($this->reactionTitle);
	}
}
