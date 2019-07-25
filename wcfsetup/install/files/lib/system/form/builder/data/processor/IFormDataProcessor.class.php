<?php
namespace wcf\system\form\builder\data\processor;
use wcf\system\form\builder\IFormDocument;

/**
 * Represents a data processor for forms that can be used to process certain form field values
 * before they are stored in database and process these values back again when an object is edited
 * the the form field values need to be set.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Data\Processor
 * @since	5.2
 */
interface IFormDataProcessor {
	/**
	 * Processes the given parameters array and returns the processed version of it that will be
	 * passed to the constructor of a database object action.
	 * 
	 * @param 	IFormDocument	$document	documents whose field data is processed 
	 * @param	array		$parameters	parameters before processing
	 * @return	array				parameters after processing
	 */
	public function processFormData(IFormDocument $document, array $parameters);
	
	/**
	 * Processes the given object data and returns the processed version of it that will be used
	 * to set the form field values.
	 *
	 * @param	IFormDocument	$document	documents whose field values will be set using the processed data
	 * @param	array		$data		data before processing
	 * @param	null|integer	$objectId	id of the object the data belongs to
	 * @return	array				data after processing
	 */
	public function processObjectData(IFormDocument $document, array $data, $objectId = null);
}
