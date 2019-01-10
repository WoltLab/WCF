<?php
namespace wcf\system\form\builder\container;

/**
 * Represents a container that is a tab of a tab menu and a tab menu itself.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Container
 * @since	5.2
 */
class TabTabMenuFormContainer extends FormContainer implements ITabFormContainer, ITabMenuFormContainer {
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__formTabTabMenuContainer';
}
