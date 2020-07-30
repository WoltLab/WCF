<?php
namespace wcf\data\devtools\missing\language\item;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit missing language item log entry.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Devtools\Missing\Language\Item
 * @since	5.3
 *
 * @method static	DevtoolsMissingLanguageItem	create(array $parameters = [])
 * @method		DevtoolsMissingLanguageItem	getDecoratedObject()
 * @mixin		DevtoolsMissingLanguageItem
 */
class DevtoolsMissingLanguageItemEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = DevtoolsMissingLanguageItem::class;
}
