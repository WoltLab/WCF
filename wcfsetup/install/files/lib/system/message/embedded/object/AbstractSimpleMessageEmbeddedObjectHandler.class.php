<?php
namespace wcf\system\message\embedded\object;
use wcf\system\html\input\HtmlInputProcessor;

/**
 * Provides default implementations for simple message embedded object handlers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Message\Embedded\Object
 */
abstract class AbstractSimpleMessageEmbeddedObjectHandler extends AbstractMessageEmbeddedObjectHandler implements ISimpleMessageEmbeddedObjectHandler {
	/**
	 * @inheritDoc
	 */
	public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData) {
		// this default implementation allows for embedded object handlers that
		// only handle the simplified syntax
		return [];
	}
}
