<?php
namespace wcf\data\language\item;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit language items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.language.item
 * @category	Community Framework
 *
 * @method	LanguageItem	getDecoratedObject()
 * @mixin	LanguageItem
 */
class LanguageItemEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = LanguageItem::class;
}
