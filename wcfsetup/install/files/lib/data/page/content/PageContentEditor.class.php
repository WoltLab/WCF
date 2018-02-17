<?php
namespace wcf\data\page\content;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit page content.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Page\Content
 * @since	3.0
 * 
 * @method static	PageContent	create(array $parameters = [])
 * @method		PageContent	getDecoratedObject()
 * @mixin		PageContent
 */
class PageContentEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = PageContent::class;
}
