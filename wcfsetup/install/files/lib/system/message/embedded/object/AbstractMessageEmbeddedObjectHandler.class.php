<?php
namespace wcf\system\message\embedded\object;
use wcf\data\object\type\ObjectType;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\util\ArrayUtil;

/**
 * Provides default implementations for message embedded object handlers.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Message\Embedded\Object
 * 
 * @method	ObjectType	getDecoratedObject()
 * @mixin	ObjectType
 */
abstract class AbstractMessageEmbeddedObjectHandler extends DatabaseObjectDecorator implements IMessageEmbeddedObjectHandler {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ObjectType::class;
	
	/**
	 * @inheritDoc
	 */
	public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData) {
		// TODO: DEBUG ONLY, remove this method!
		return [];
	}
	
}
