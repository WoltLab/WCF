<?php
namespace wcf\system\importer;
use wcf\data\smiley\SmileyEditor;

/**
 * Imports smilies.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class SmileyImporter implements IImporter {
	/**
	 * @see wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		// copy smiley
		$data['smileyPath'] = 'images/smilies/'.basename($additionalData['fileLocation']);
		if (!@copy($additionalData['fileLocation'], WCF_DIR.$data['smileyPath'])) return 0;
		
		// save smiley
		$smiley = SmileyEditor::create($data);
		
		return $smiley->smileyID;
	}
}
