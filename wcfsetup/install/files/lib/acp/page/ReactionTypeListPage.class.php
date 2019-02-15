<?php
namespace wcf\acp\page;
use wcf\data\reaction\type\ReactionTypeList;
use wcf\page\MultipleLinkPage;

/**
 * Shows the list of reaction types.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 *
 * @property	ReactionTypeList	$objectList
 */
class ReactionTypeListPage extends MultipleLinkPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.reactionType.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_LIKE'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.reaction.canManageReactionType'];
	
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'showOrder ASC, reactionTypeID ASC';
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = ReactionTypeList::class;
}
