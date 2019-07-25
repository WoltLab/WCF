<?php
namespace wcf\system\form\builder\data;
use wcf\data\IStorableObject;
use wcf\system\form\builder\data\processor\IFormDataProcessor;
use wcf\system\form\builder\IFormDocument;

/**
 * Represents a data handler that extracts the data of a form document into an array
 * that can be passed to the constructor of a database object action.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Data
 * @since	5.2
 */
interface IFormDataHandler {
	/**
	 * Adds the given field data processor to this data handler and returns
	 * this data handler.
	 * 
	 * @param	IFormDataProcessor $processor added field data processor
	 *
	 * @return	static						this data handler
	 */
	public function addProcessor(IFormDataProcessor $processor);
	
	/**
	 * Returns the data from the given form that is passed as the parameters
	 * array to a database object action.
	 * 
	 * @param	IFormDocument	$document	processed form document
	 * @return	array				data passed to database object action
	 */
	public function getFormData(IFormDocument $document);
	
	/**
	 * Returns the processed data of the given object that will be used to set the form fields'
	 * value.
	 *
	 * @param	IFormDocument	$document	form document whose form field values will be set
	 * @param	IStorableObject	$object		object from which the data is extracted
	 * @return	array				processed object data
	 */
	public function getObjectData(IFormDocument $document, IStorableObject $object);
}
