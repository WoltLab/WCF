<?php
namespace wcf\data\like\object;
use wcf\data\object\type\ObjectType;
use wcf\data\DatabaseObjectDecorator;

/**
 * Provides a default implementation for like objects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.like.object
 * @category	Community Framework
 */
abstract class AbstractLikeObject extends DatabaseObjectDecorator implements ILikeObject {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\like\object\LikeObject';
	
	/**
	 * object type
	 * @var	\wcf\data\object\type\ObjectType
	 */
	protected $objectType = null;
	
	/**
	 * @see	\wcf\data\like\object\ILikeObject::updateLikeCounter()
	 */
	public function updateLikeCounter($cumulativeLikes) { }
	
	/**
	 * @see	\wcf\data\like\object\ILikeObject::getObjectType()
	 */
	public function getObjectType() {
		return $this->objectType;
	}
	
	/**
	 * @see	\wcf\data\like\object\ILikeObject::setObjectType()
	 */
	public function setObjectType(ObjectType $objectType) {
		$this->objectType = $objectType;
	}
}
