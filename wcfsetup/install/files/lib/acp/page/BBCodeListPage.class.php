<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;

/**
 * Lists the available BBCodes.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class BBCodeListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.bbcode.list';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'bbcodeTag';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.bbcode.canManageBBCode'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = 'wcf\data\bbcode\BBCodeList';
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'bbcodeList';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['bbcodeID', 'bbcodeTag', 'className'];
}
