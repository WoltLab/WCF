<?php
namespace wcf\data\acp\template;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit ACP templates.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Template
 * 
 * @method	ACPTemplate	getDecoratedObject()
 * @mixin	ACPTemplate
 */
class ACPTemplateEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ACPTemplate::class;
}
