<?php
namespace wcf\data\user\rank;
use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a user rank.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Rank
 *
 * @property-read	integer		$rankID			unique id of the user rank
 * @property-read	integer		$groupID		id of the user group to which the user rank belongs
 * @property-read	integer		$requiredPoints		minimum number of user activity points required for a user to get the user rank
 * @property-read	string		$rankTitle		title of the user rank or name of the language item which contains the rank
 * @property-read	string		$cssClassName		css class name used when displaying the user rank
 * @property-read	string		$rankImage		(WCF relative) path to the image displayed next to the rank or empty if no rank image exists
 * @property-read	integer		$repeatImage		number of times the rank image is displayed
 * @property-read	integer		$requiredGender		numeric representation of the user's gender required for the user rank (see `UserProfile::GENDER_*` constants) or 0 if no specific gender is required
 * @property-read	integer		$hideTitle		hides the generic title of the rank, but not custom titles, `0` to show the title at all times
 */
class UserRank extends DatabaseObject {
	/**
	 * Returns the image of this user rank.
	 * 
	 * @return	string		html code
	 */
	public function getImage() {
		if ($this->rankImage) {
			$image = '<img src="'.(!preg_match('~^(/|https?://)~i', $this->rankImage) ? WCF::getPath() : '').StringUtil::encodeHTML($this->rankImage).'" alt="">';
			if ($this->repeatImage > 1) $image = str_repeat($image, $this->repeatImage);
			return $image;
		}
		
		return '';
	}
	
	/**
	 * Returns true if the generic rank title should be displayed.
	 * 
	 * @return      boolean
	 */
	public function showTitle() {
		return !$this->rankImage || !$this->hideTitle;
	}
}
