<?php
namespace wcf\data\box;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a box.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.box
 * @category	Community Framework
 */
class Box extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'box';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'boxID';
	
	/**
	 * available box types
	 * @var string[]
	 */
	public static $availableBoxTypes = ['text', 'html', 'system', 'menu'];
	
	/**
	 * available box positions
	 * @var string[]
	 */
	public static $availablePositions = ['header', 'headerBoxes', 'top', 'sidebarLeft', 'contentTop', 'sidebarRight', 'contentBottom', 'bottom', 'footerBoxes', 'footer'];
	
	/**
	 * Returns true if the active user can delete this box.
	 * 
	 * @return boolean
	 */
	public function canDelete() {
		if (WCF::getSession()->getPermission('admin.content.cms.canManageBox') && !$this->originIsSystem) {
			return true;
		}
			
		return false;
	}
	
	/**
	 * Returns the box content.
	 *
	 * @return array
	 */
	public function getBoxContent() {
		$content = array();
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_box_content
			WHERE	boxID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->boxID));
		while ($row = $statement->fetchArray()) {
			$content[($row['languageID'] ?: 0)] = [
				'title' => $row['title'],
				'content' => $row['content']
			];
		}
	
		return $content;
	}
	
	/**
	 * Returns the box with the given name.
	 *
	 * @param	string		$name
	 * @return	Box
	 */
	public static function getBoxByName($name) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_box
			WHERE	name = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($name));
		$row = $statement->fetchArray();
		if ($row !== false) return new Box(null, $row);
	
		return null;
	}
}
