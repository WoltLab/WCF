<?php
namespace wcf\data\spider;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit spiders.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Spider
 * 
 * @method static	Spider		create(array $parameters = [])
 * @method		Spider		getDecoratedObject()
 * @mixin		Spider
 */
class SpiderEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Spider::class;
}
