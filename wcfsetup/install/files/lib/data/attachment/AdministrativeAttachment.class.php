<?php
namespace wcf\data\attachment;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents an attachment.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.attachment
 * @category	Community Framework
 */
class AdministrativeAttachment extends DatabaseObjectDecorator {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\attachment\Attachment';
	
	/**
	 * container object
	 * @var	\wcf\data\IUserContent
	 */
	protected $containerObject = null;
	
	/**
	 * true if container object has been loaded
	 * @var	boolean
	 */
	protected $containerObjectLoaded = false;
	
	/**
	 * Gets the container object of this attachment.
	 * 
	 * @return	\wcf\data\IUserContent
	 */
	public function getContainerObject() {
		if (!$this->containerObjectLoaded) {
			$this->containerObjectLoaded = true;
			
			$objectType = ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID);
			$this->containerObject = $objectType->getProcessor()->getObject($this->objectID);
		}
		
		return $this->containerObject;
	}
}
