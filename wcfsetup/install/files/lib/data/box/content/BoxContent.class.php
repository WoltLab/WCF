<?php
namespace wcf\data\box\content;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a box content.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Box\Content
 * @since	3.0
 *
 * @property-read	integer		$boxContentID
 * @property-read	integer		$boxID
 * @property-read	integer		$languageID
 * @property-read	string		$title
 * @property-read	string		$content
 * @property-read	integer		$imageID
 * @property-read	integer		$hasEmbeddedObjects
 */
class BoxContent extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'box_content';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'boxContentID';
	
	/**
	 * Returns a certain box content.
	 *
	 * @param       integer         $boxID
	 * @param       integer         $languageID
	 * @return      BoxContent|null
	 */
	public static function getBoxContent($boxID, $languageID) {
		if ($languageID !== null) {
			$sql = "SELECT  *
				FROM    wcf" . WCF_N . "_box_content
				WHERE   boxID = ?
					AND languageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$boxID, $languageID]);
		}
		else {
			$sql = "SELECT  *
				FROM    wcf" . WCF_N . "_box_content
				WHERE   boxID = ?
					AND languageID IS NULL";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$boxID]);
		}
		
		if (($row = $statement->fetchSingleRow()) !== false) {
			return new BoxContent(null, $row);
		}
		
		return null;
	}
}
