<?php
namespace wcf\data\object\type;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\IDatabaseObjectProcessor;

/**
 * Abstract implementation of an object type processor.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Object\Type
 * 
 * @method	ObjectType	getDecoratedObject()
 * @mixin	ObjectType
 */
abstract class AbstractObjectTypeProcessor extends DatabaseObjectDecorator implements IDatabaseObjectProcessor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ObjectType::class;
}
