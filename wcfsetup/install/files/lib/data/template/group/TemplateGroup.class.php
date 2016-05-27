<?php
namespace wcf\data\template\group;
use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a template group. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template.group
 * @category	Community Framework
 *
 * @property-read	integer		$templateGroupID
 * @property-read	integer|null	$parentTemplateGroupID
 * @property-read	string		$templateGroupName
 * @property-read	string		$templateGroupFolderName
 */
class TemplateGroup extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'template_group';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'templateGroupID';
	
	protected static $templateGroupStructure = null;
	protected static $selectList = null;
	
	/**
	 * Creates a select list of all template groups.
	 * 
	 * @param	integer[]	$ignore		Array of template group ids that should be excluded with all of their children
	 * @param	integer		$initialDepth	Specifies the initial indentation depth of the list
	 * @return	array
	 */
	public static function getSelectList($ignore = [], $initialDepth = 0) {
		if (self::$templateGroupStructure === null) {
			self::$templateGroupStructure = [];
			
			$sql = "SELECT		templateGroupID, templateGroupName, parentTemplateGroupID
				FROM		wcf".WCF_N."_template_group
				ORDER BY	templateGroupName ASC";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			while ($row = $statement->fetchArray()) {
				self::$templateGroupStructure[$row['parentTemplateGroupID'] ?: 0][] = new TemplateGroup(null, $row);
			}
		}
		
		self::$selectList = [];
		self::makeSelectList(0, $initialDepth, $ignore);
		
		return self::$selectList;
	}
	
	/**
	 * Generates the select list.
	 * 
	 * @param	integer		$parentID		id of the parent template group
	 * @param	integer		$depth			current list depth
	 * @param	array		$ignore			list of template group ids to ignore in result
	 */
	protected static function makeSelectList($parentID = 0, $depth = 0, $ignore = []) {
		if (!isset(self::$templateGroupStructure[$parentID ?: 0])) return;
		
		foreach (self::$templateGroupStructure[$parentID ?: 0] as $templateGroup) {
			if (!empty($ignore) && in_array($templateGroup->templateGroupID, $ignore)) continue;
			
			// we must encode html here because the htmloptions plugin doesn't do it
			$title = StringUtil::encodeHTML($templateGroup->templateGroupName);
			if ($depth > 0) $title = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth). ' ' . $title;
			
			self::$selectList[$templateGroup->templateGroupID] = $title;
			self::makeSelectList($templateGroup->templateGroupID, $depth + 1, $ignore);
		}
	}
}
