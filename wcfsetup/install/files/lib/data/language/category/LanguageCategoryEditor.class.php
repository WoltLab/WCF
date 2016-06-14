<?php
namespace wcf\data\language\category;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit language categories.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Language\Category
 * 
 * @method	LanguageCategory	getDecoratedObject()
 * @mixin	LanguageCategory
 */
class LanguageCategoryEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = LanguageCategory::class;
}
