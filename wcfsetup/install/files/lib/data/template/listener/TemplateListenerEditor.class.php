<?php
namespace wcf\data\template\listener;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit template listeners.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template.listener
 * @category	Community Framework
 * 
 * @method	TemplateListener	getDecoratedObject()
 * @mixin	TemplateListener
 */
class TemplateListenerEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = TemplateListener::class;
}
