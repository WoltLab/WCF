<?php
namespace wcf\system\importer;

/**
 * Basic interface for all importer.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
interface IImporter {
	/**
	 * Imports a data set.
	 * 
	 * @param	mixed		$oldID
	 * @param	array		$data
	 * @param	array		$additionalData
	 * @return	mixed		new id
	 */
	public function import($oldID, array $data, array $additionalData = []);
	
	/**
	 * Returns database object class name.
	 * 
	 * @return	string
	 */
	public function getClassName();
}
