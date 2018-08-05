<?php
namespace wcf\system\form\builder\field\data;
use wcf\system\form\builder\IFormDocument;

/**
 * Represents a data processor for form fields that populates or manipulates the
 * parameters array passed to the constructor of a database object action.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\Data
 * @since	3.2
 */
interface IFormFieldDataProcessor {
	/**
	 * Processes the given parameters array and returns the processed version of it.
	 * 
	 * @param 	IFormDocument	$document	documents whose field data is processed 
	 * @param	array		$parameters	parameters before processing
	 * @return	array				parameters after processing
	 */
	public function __invoke(IFormDocument $document, array $parameters);
}
