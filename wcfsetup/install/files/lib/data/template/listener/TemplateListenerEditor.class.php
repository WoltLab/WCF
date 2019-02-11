<?php
namespace wcf\data\template\listener;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\TemplateListenerCodeCacheBuilder;
use wcf\system\language\LanguageFactory;

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
class TemplateListenerEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = TemplateListener::class;
	
	/**
	 * @inheritDoc
	 * @since	5.2
	 */
	public static function resetCache() {
		TemplateListenerCodeCacheBuilder::getInstance()->reset();
		
		// delete compiled templates
		LanguageFactory::getInstance()->deleteLanguageCache();
	}
}
