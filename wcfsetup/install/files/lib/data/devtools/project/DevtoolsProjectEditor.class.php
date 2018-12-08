<?php
namespace wcf\data\devtools\project;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit devtool projects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Devtools\Project
 * @since	3.1
 * 
 * @method static	DevtoolsProject	create(array $parameters = [])
 * @method		DevtoolsProject	getDecoratedObject()
 * @mixin		DevtoolsProject
 */
class DevtoolsProjectEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = DevtoolsProject::class;
}
