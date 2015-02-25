<?php
namespace wcf\data\template;
use wcf\data\package\PackageCache;
use wcf\data\DatabaseObject;
use wcf\system\application\ApplicationHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Represents a template.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template
 * @category	Community Framework
 */
class Template extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'template';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'templateID';
	
	/**
	 * @see	\wcf\data\DatabaseObject::__construct()
	 */
	public function __construct($id, $row = null, DatabaseObject $object = null) {
		if ($id !== null) {
			$sql = "SELECT		template.*, template_group.templateGroupFolderName,
						package.package
				FROM		wcf".WCF_N."_template template
				LEFT JOIN	wcf".WCF_N."_template_group template_group
				ON		(template_group.templateGroupID = template.templateGroupID)
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = template.packageID)
				WHERE		template.templateID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($id));
			$row = $statement->fetchArray();
			
			if ($row !== false) {
				// get relative directory of the template the application
				// belongs to
				if ($row['application'] != 'wcf') {
					$application = ApplicationHandler::getInstance()->getApplication($row['application']);
				}
				else {
					$application = ApplicationHandler::getInstance()->getWCF();
				}
				$row['packageDir'] = PackageCache::getInstance()->getPackage($application->packageID)->packageDir;
			}
			else {
				$row = array();
			}
		}
		else if ($object !== null) {
			$row = $object->data;
		}
		
		$this->handleData($row);
	}
	
	/**
	 * Returns the path to this template.
	 * 
	 * @return	string
	 */
	public function getPath() {
		$path = FileUtil::getRealPath(WCF_DIR . $this->packageDir) . 'templates/' . $this->templateGroupFolderName . $this->templateName . '.tpl';
		return $path;
	}
	
	/**
	 * Returns the source of this template.
	 * 
	 * @return	string
	 */
	public function getSource() {
		return @file_get_contents($this->getPath());
	}
	
	/**
	 * Searches in templates.
	 * 
	 * @param	string		$search		search query
	 * @param	string		$replace
	 * @param	array		$templateIDs
	 * @param	boolean		$invertTemplates
	 * @param	boolean		$useRegex
	 * @param	boolean		$caseSensitive
	 * @param	boolean		$invertSearch
	 * @return	array
	 */
	public static function search($search, $replace = null, $templateIDs = null, $invertTemplates = 0, $useRegex = 0, $caseSensitive = 0, $invertSearch = 0) {
		// get available template ids
		$results = array();
		$availableTemplateIDs = array();
		$sql = "SELECT		templateName, templateID, templateGroupID, packageID
			FROM		wcf".WCF_N."_template
					".($replace !== null ? "WHERE templateGroupID <> 0" : "");
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			if (!isset($availableTemplateIDs[$row['templateName'].'-'.$row['templateGroupID']]) || PACKAGE_ID == $row['packageID']) {
				$availableTemplateIDs[$row['templateName'].'-'.$row['templateGroupID']] = $row['templateID'];
			}
		}
		
		// get templates
		if (empty($availableTemplateIDs)) return $results;
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("template.templateID IN (?)", array($availableTemplateIDs));
		if ($templateIDs !== null) $conditions->add("template.templateID ".($invertTemplates ? "NOT " : "")." IN (?)", array($templateIDs));
		
		$sql = "SELECT		template.*, group.templateGroupFolderName, package.packageDir
			FROM		wcf".WCF_N."_template template
			LEFT JOIN	wcf".WCF_N."_template_group group
			ON		(group.templateGroupID = template.templateGroupID)
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = template.packageID)
			".$conditions."
			ORDER BY	templateName ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		unset($availableTemplateIDs);
		while ($row = $statement->fetchArray()) {
			$template = new TemplateEditor(null, $row);
			if ($replace === null) {
				// search
				if ($useRegex) $matches = (intval(preg_match('/'.$search.'/s'.(!$caseSensitive ? 'i' : ''), $template->getSource())) !== 0);
				else {
					if ($caseSensitive) $matches = (mb_strpos($template->getSource(), $search) !== false);
					else $matches = (mb_strripos($template->getSource(), $search) !== false);
				}
				
				if (($matches && !$invertSearch) || (!$matches && $invertSearch)) {
					$results[] = $row;
				}
			}
			else {
				// search and replace
				$matches = 0;
				if ($useRegex) {
					$newSource = preg_replace('/'.$search.'/s'.(!$caseSensitive ? 'i' : ''), $replace, $template->getSource(), -1, $matches);
				}
				else {
					if ($caseSensitive) $newSource = str_replace($search, $replace, $template->getSource(), $matches);
					else $newSource = StringUtil::replaceIgnoreCase($search, $replace, $template->getSource(), $matches);
				}
				
				if ($matches > 0) {
					$template->setSource($newSource);
					$row['matches'] = $matches;
					$results[] = $row;
				}
			}
		}
		
		return $results;
	}
}
