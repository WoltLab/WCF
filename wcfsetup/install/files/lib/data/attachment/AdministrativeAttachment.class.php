<?php
namespace wcf\data\attachment;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\IUserContent;

/**
 * Represents an attachment.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.attachment
 * @category	Community Framework
 * 
 * @method	Attachment	getDecoratedObject()
 * @mixin	Attachment
 */
class AdministrativeAttachment extends DatabaseObjectDecorator {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Attachment::class;
	
	/**
	 * container object
	 * @var	IUserContent
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
	 * @return	IUserContent
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
