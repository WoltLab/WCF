<?php
namespace wcf\system\form\builder\container;
use wcf\data\IStorableObject;
use wcf\system\form\builder\IFormChildNode;
use wcf\system\form\builder\IFormElement;
use wcf\system\form\builder\IFormParentNode;

/**
 * Represents a container whose only purpose is to contain other form nodes.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Container
 * @since	5.2
 */
interface IFormContainer extends IFormChildNode, IFormElement, IFormParentNode {
	/**
	 * This method is called by `IFormDocument::loadValue()` to inform the container that object
	 * data is being loaded.
	 * 
	 * This method is *not* intended to generally call `IFormField::loadValue()` on its form field
	 * children as these methods are already called by `IFormDocument::loadValuesFromObject()`.
	 *
	 * @param	array			$data		data from which the values are extracted
	 * @param	IStorableObject		$object		object the data belongs to
	 * @return	static					this container
	 */
	public function loadValues(array $data, IStorableObject $object);
}
