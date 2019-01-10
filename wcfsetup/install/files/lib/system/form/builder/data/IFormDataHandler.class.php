<?php
namespace wcf\system\form\builder\data;
use wcf\system\form\builder\field\data\processor\IFormFieldDataProcessor;
use wcf\system\form\builder\IFormDocument;

/**
 * Represents a data handler that extracts the data of a form document into an array
 * that can be passed to the constructor of a database object action.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Data
 * @since	5.2
 */
interface IFormDataHandler {
	/**
	 * Adds the given field data processor to this data handler and returns
	 * this data handler.
	 * 
	 * @param	IFormFieldDataProcessor		$processor	added field data processor
	 * @return	static						this data handler
	 */
	public function add(IFormFieldDataProcessor $processor);
	
	/**
	 * Returns the data from the given form that is passed as the parameters
	 * array to a database object action.
	 * 
	 * @param	IFormDocument	$document	processed form document
	 * @return	array				data passed to database object action
	 */
	public function getData(IFormDocument $document);
}
