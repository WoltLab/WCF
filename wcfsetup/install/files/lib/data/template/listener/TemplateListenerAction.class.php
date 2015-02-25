<?php
namespace wcf\data\template\listener;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes template listener-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template.listener
 * @category	Community Framework
 */
class TemplateListenerAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\template\listener\TemplateListenerEditor';
}
