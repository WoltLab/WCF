<?php
namespace wcf\data\bbcode\media\provider;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\SystemException;
use wcf\system\Regex;
use wcf\util\StringUtil;

/**
 * Executes BBCode media provider-related actions.
 * 
 * @author	Tim Duesterhus
 * @copyright	2011-2013 Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.bbcode
 * @subpackage	data.bbcode.media.provider
 * @category	Community Framework
 */
class BBCodeMediaProviderAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\bbcode\media\provider\BBCodeMediaProviderEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.content.bbcode.canManageBBCode');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.content.bbcode.canManageBBCode');
}
