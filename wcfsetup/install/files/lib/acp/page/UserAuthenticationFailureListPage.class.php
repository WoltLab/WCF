<?php
namespace wcf\acp\page;
use wcf\data\user\authentication\failure\UserAuthenticationFailureList;
use wcf\page\SortablePage;

/**
 * Shows a list of user authentication failures.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	UserAuthenticationFailureList	$objectList
 */
class UserAuthenticationFailureListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.log.authentication.failure';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.management.canViewLog'];
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['ENABLE_USER_AUTHENTICATION_FAILURE'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'time';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['failureID', 'environment', 'userID', 'username', 'time', 'ipAddress', 'userAgent'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = UserAuthenticationFailureList::class;
}
