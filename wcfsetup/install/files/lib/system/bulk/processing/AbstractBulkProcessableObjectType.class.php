<?php
namespace wcf\system\bulk\processing;
use wcf\data\object\type\AbstractObjectTypeProcessor;
use wcf\system\WCF;

/**
 * Abstract implementation of a bulk processable object type.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bulk\Processing
 * @since	3.0
 */
abstract class AbstractBulkProcessableObjectType extends AbstractObjectTypeProcessor implements IBulkProcessableObjectType {
	/**
	 * name of the object type definition for the bulk actions
	 * @var	string
	 */
	protected $actionObjectTypeDefinition = '';
	
	/**
	 * name of the object type definition for the object conditions
	 * @var	string
	 */
	protected $conditionObjectTypeDefinition = '';

	/**
	 * name of the prefix of the language items used in the interface
	 * @var	string
	 */
	protected $languageItemPrefix = '';
	
	/**
	 * name of the conditions template
	 * @var	string
	 */
	protected $templateName = '';
	
	/**
	 * @inheritDoc
	 */
	public function getActionObjectTypeDefinition() {
		if (empty($this->actionObjectTypeDefinition)) {
			$this->actionObjectTypeDefinition = $this->object->objectType.'.action';
		}
		
		return $this->actionObjectTypeDefinition;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getConditionHTML() {
		return WCF::getTPL()->fetch($this->templateName, explode('\\', get_class($this))[0]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getConditionObjectTypeDefinition() {
		if (empty($this->conditionObjectTypeDefinition)) {
			$this->conditionObjectTypeDefinition = $this->object->objectType.'.condition';
		}
		
		return $this->conditionObjectTypeDefinition;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLanguageItemPrefix() {
		if (empty($this->languageItemPrefix)) {
			$application = explode('\\', get_class($this))[0];
			$objectTypePieces = explode('.', $this->object->objectType);
			
			$this->languageItemPrefix = $application.'.acp.'.end($objectTypePieces).'.bulkProcessing';
		}
		
		return $this->languageItemPrefix;
	}
}
