<?php
namespace wcf\system\package\plugin;
use wcf\data\acp\menu\item\ACPMenuItemEditor;

/**
 * Installs, updates and deletes ACP menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class ACPMenuPackageInstallationPlugin extends AbstractMenuPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = ACPMenuItemEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		$returnValue = parent::prepareImport($data);
		
		$returnValue['icon'] = (isset($data['elements']['icon'])) ? $data['elements']['icon'] : '';
		
		return $returnValue;
	}
	
	/**
	 * @see	\wcf\system\package\plugin\IPackageInstallationPlugin::getDefaultFilename()
	 * @since	3.0
	 */
	public static function getDefaultFilename() {
		return 'acpMenu.xml';
	}
}
