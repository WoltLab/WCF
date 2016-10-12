<?php
namespace wcf\system\bbcode;
use wcf\data\bbcode\BBCode;
use wcf\data\DatabaseObjectDecorator;

/**
 * Provides an abstract implementation for bbcodes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 * 
 * @method	BBCode	getDecoratedObject()
 * @mixin	BBCode
 */
abstract class AbstractBBCode extends DatabaseObjectDecorator implements IBBCode {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = BBCode::class;
}
