<?php
namespace wcf\data\acp\template;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit ACP templates.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.template
 * @category	Community Framework
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
