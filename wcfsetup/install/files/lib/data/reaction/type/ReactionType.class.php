<?php
namespace wcf\data\reaction\type;
use wcf\data\DatabaseObject;
use wcf\data\ITitledObject;
use wcf\system\WCF;

/**
 * Represents a reaction type.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Reaction\Type
 * @since       5.2
 *
 * @property-read	integer		$reactionTypeID		unique id of the reaction type
 * @property-read	string		$title
 * @property-read	integer		$showOrder		position of the reaction type in relation to the other reaction types
 * @property-read	string		$iconFile		the file location of the icon
 * @property-read       integer		$isDisabled		is `1` if the reaction type is disabled and thus not shown, otherwise `0`
 */
class ReactionType extends DatabaseObject implements ITitledObject {
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
	 * Returns the absolute location of the icon file. 
	 * 
	 * @return string[]
	 */
	public function getIconFileUploadFileLocations() {
		return [WCF_DIR . 'images/reaction/'. $this->iconFile];
	}
}
