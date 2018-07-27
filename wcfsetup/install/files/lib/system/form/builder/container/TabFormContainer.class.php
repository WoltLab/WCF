<?php
namespace wcf\system\form\builder\container;

/**
 * Represents a container that is a tab of a tab menu.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Container
 * @since	3.2
 */
class TabFormContainer extends FormContainer implements ITabFormContainer {
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__tabFormContainer';
}
