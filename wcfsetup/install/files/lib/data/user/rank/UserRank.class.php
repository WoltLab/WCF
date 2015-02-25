<?php
namespace wcf\data\user\rank;
use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a user rank.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.rank
 * @category	Community Framework
 */
class UserRank extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'user_rank';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'rankID';
	
	/**
	 * Returns the image of this user rank.
	 * 
	 * @return	string		html code
	 */
	public function getImage() {
		if ($this->rankImage) {
			$image = '<img src="'.(!preg_match('~^(/|https?://)~i', $this->rankImage) ? WCF::getPath() : '').StringUtil::encodeHTML($this->rankImage).'" alt="" />';
			if ($this->repeatImage > 1) $image = str_repeat($image, $this->repeatImage);
			return $image;
		}
		
		return '';
	}
}
