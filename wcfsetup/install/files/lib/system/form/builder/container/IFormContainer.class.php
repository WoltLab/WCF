<?php
namespace wcf\system\form\builder\container;
use wcf\system\form\builder\IFormChildNode;
use wcf\system\form\builder\IFormElement;
use wcf\system\form\builder\IFormParentNode;

/**
 * Represents a container whose only purpose is to contain other form nodes.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Container
 * @since	5.2
 */
interface IFormContainer extends IFormChildNode, IFormElement, IFormParentNode {}
