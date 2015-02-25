<?php
namespace wcf\system\importer;
use wcf\data\smiley\SmileyEditor;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Imports smilies.
 * 
 * @author	Tim Duesterhus, Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class SmileyImporter extends AbstractImporter {
	/**
	 * @see	\wcf\system\importer\AbstractImporter::$className
	 */
	protected $className = 'wcf\data\smiley\Smiley';
	
	/**
	 * known smiley codes
	 * 
	 * @var	array<string>
	 */
	public $knownCodes = array();
	
	/**
	 * Reads out known smiley codes.
	 */
	public function __construct() {
		$sql = "SELECT	smileyID, smileyCode, aliases
			FROM	wcf".WCF_N."_smiley";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		while ($row = $statement->fetchArray()) {
			$known = array();
			if (!empty($row['aliases'])) {
				$known = explode("\n", $row['aliases']);
			}
			$known[] = $row['smileyCode'];
				
			foreach ($known as $smileyCode) {
				$this->knownCodes[mb_strtolower($smileyCode)] = $row['smileyID'];
			}
		}
	}
	
	/**
	 * @see	\wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		// copy smiley
		$data['smileyPath'] = 'images/smilies/'.basename($additionalData['fileLocation']);
		if (!@copy($additionalData['fileLocation'], WCF_DIR.$data['smileyPath'])) return 0;
		
		// check smileycode
		if (isset($this->knownCodes[mb_strtolower($data['smileyCode'])])) return $this->knownCodes[mb_strtolower($data['smileyCode'])];
		
		$data['packageID'] = 1;
		if (!isset($data['aliases'])) $data['aliases'] = '';
		
		// check aliases
		$aliases = array();
		if (!empty($data['aliases'])) {
			$aliases = explode("\n", StringUtil::unifyNewlines($data['aliases']));
			foreach ($aliases as $key => $alias) {
				if (isset($this->knownCodes[mb_strtolower($alias)])) unset($aliases[$key]);
			}
			$data['aliases'] = implode("\n", $aliases);
		}
		
		// get category id
		if (!empty($data['categoryID'])) $data['categoryID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.smiley.category', $data['categoryID']);
		
		// save smiley
		$smiley = SmileyEditor::create($data);
		
		// add smileyCode + aliases to knownCodes
		$this->knownCodes[mb_strtolower($data['smileyCode'])] = $smiley->smileyID;
		foreach ($aliases as $alias) {
			$this->knownCodes[mb_strtolower($alias)] = $smiley->smileyID;
		}
		
		return $smiley->smileyID;
	}
}
