<?php
namespace wcf\acp\action;
use wcf\data\search\SearchEditor;
use wcf\action\AbstractAction;
use wcf\system\exception\NamedUserException;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Provides special search options.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category	Community Framework
 */
class UserQuickSearchAction extends AbstractAction {
	/**
	 * @see	\wcf\action\AbstractAction::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canEditUser');
	
	/**
	 * search mode
	 * @var	string
	 */
	public $mode = '';
	
	/**
	 * matches
	 * @var	array<integer>
	 */
	public $matches = array();
	
	/**
	 * results per page
	 * @var	integer
	 */
	public $itemsPerPage = 50;
	
	/**
	 * shown columns
	 * @var	array<string>
	 */
	public $columns = array('email', 'registrationDate');
	
	/**
	 * sort field
	 * @var	string
	 */
	public $sortField = 'username';
	
	/**
	 * sort order
	 * @var	string
	 */
	public $sortOrder = 'ASC';
	
	/**
	 * number of results
	 * @var	integer
	 */
	public $maxResults = 500;
	
	/**
	 * @see	\wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['mode'])) $this->mode = $_REQUEST['mode'];
	}
	
	/**
	 * @see	\wcf\action\IAction::execute();
	 */
	public function execute() {
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.user.search');
		
		parent::execute();
		
		switch ($this->mode) {
			case 'banned':
				$sql = "SELECT		user_table.userID
					FROM		wcf".WCF_N."_user user_table
					LEFT JOIN	wcf".WCF_N."_user_option_value option_value
					ON		(option_value.userID = user_table.userID)
					WHERE		banned = ?";
				$statement = WCF::getDB()->prepareStatement($sql, $this->maxResults);
				$statement->execute(array(1));
				while ($row = $statement->fetchArray()) {
					$this->matches[] = $row['userID'];
				}
				break;
				
			case 'newest':
				$this->maxResults = 100;
				$this->sortField = 'registrationDate';
				$this->sortOrder = 'DESC';
				$sql = "SELECT		user_table.userID
					FROM		wcf".WCF_N."_user user_table
					LEFT JOIN	wcf".WCF_N."_user_option_value option_value
					ON		(option_value.userID = user_table.userID)
					ORDER BY	user_table.registrationDate DESC";
				$statement = WCF::getDB()->prepareStatement($sql, $this->maxResults);
				$statement->execute();
				while ($row = $statement->fetchArray()) {
					$this->matches[] = $row['userID'];
				}
				break;
			
			case 'disabled':
				$this->sortField = 'registrationDate';
				$this->sortOrder = 'DESC';
				$sql = "SELECT		user_table.userID
					FROM		wcf".WCF_N."_user user_table
					LEFT JOIN	wcf".WCF_N."_user_option_value option_value
					ON		(option_value.userID = user_table.userID)
					WHERE		activationCode <> ?
					ORDER BY	user_table.registrationDate DESC";
				$statement = WCF::getDB()->prepareStatement($sql, $this->maxResults);
				$statement->execute(array(0));
				while ($row = $statement->fetchArray()) {
					$this->matches[] = $row['userID'];
				}
				break;
			
			case 'disabledAvatars':
				$sql = "SELECT		user_table.userID
					FROM		wcf".WCF_N."_user user_table
					LEFT JOIN	wcf".WCF_N."_user_option_value option_value
					ON		(option_value.userID = user_table.userID)
					WHERE		disableAvatar = ?";
				$statement = WCF::getDB()->prepareStatement($sql, $this->maxResults);
				$statement->execute(array(1));
				while ($row = $statement->fetchArray()) {
					$this->matches[] = $row['userID'];
				}
				break;
					
			case 'disabledSignatures':
				$sql = "SELECT		user_table.userID
					FROM		wcf".WCF_N."_user user_table
					LEFT JOIN	wcf".WCF_N."_user_option_value option_value
					ON		(option_value.userID = user_table.userID)
					WHERE		disableSignature = ?";
				$statement = WCF::getDB()->prepareStatement($sql, $this->maxResults);
				$statement->execute(array(1));
				while ($row = $statement->fetchArray()) {
					$this->matches[] = $row['userID'];
				}
				break;
		}
		
		if (empty($this->matches)) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.acp.user.search.error.noMatches'));
		}
		
		// store search result in database
		$data = serialize(array(
			'matches' => $this->matches,
			'itemsPerPage' => $this->itemsPerPage,
			'columns' => $this->columns
		));
		
		$search = SearchEditor::create(array(
			'userID' => WCF::getUser()->userID,
			'searchData' => $data,
			'searchTime' => TIME_NOW,
			'searchType' => 'users'
		));
		$this->executed();
		
		// forward to result page
		$url = LinkHandler::getInstance()->getLink('UserList', array('id' => $search->searchID), 'sortField='.rawurlencode($this->sortField).'&sortOrder='.rawurlencode($this->sortOrder));
		HeaderUtil::redirect($url);
		exit;
	}
}
