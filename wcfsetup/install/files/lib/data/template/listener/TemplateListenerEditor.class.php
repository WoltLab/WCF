<?php
namespace wcf\data\template\listener;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit template listeners.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Template\Listener
 * 
 * @method static	TemplateListener	create(array $parameters = [])
 * @method		TemplateListener	getDecoratedObject()
 * @mixin		TemplateListener
 */
class TemplateListenerEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = TemplateListener::class;
}
