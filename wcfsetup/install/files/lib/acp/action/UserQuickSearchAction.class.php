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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Action
 */
class UserQuickSearchAction extends AbstractAction {
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.user.canEditUser'];
	
	/**
	 * search mode
	 * @var	string
	 */
	public $mode = '';
	
	/**
	 * matches
	 * @var	integer[]
	 */
	public $matches = [];
	
	/**
	 * results per page
	 * @var	integer
	 */
	public $itemsPerPage = 50;
	
	/**
	 * shown columns
	 * @var	string[]
	 */
	public $columns = ['registrationDate', 'lastActivityTime'];
	
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
	public $maxResults = 2000;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['mode'])) $this->mode = $_REQUEST['mode'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.user.search');
		
		parent::execute();
		
		// add email column for authorized users
		if (WCF::getSession()->getPermission('admin.user.canEditMailAddress')) {
			array_unshift($this->columns, 'email');
		}
		
		switch ($this->mode) {
			case 'banned':
				$sql = "SELECT		user_table.userID
					FROM		wcf".WCF_N."_user user_table
					LEFT JOIN	wcf".WCF_N."_user_option_value option_value
					ON		(option_value.userID = user_table.userID)
					WHERE		banned = ?";
				$statement = WCF::getDB()->prepareStatement($sql, $this->maxResults);
				$statement->execute([1]);
				$this->matches = $statement->fetchAll(\PDO::FETCH_COLUMN);
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
				$this->matches = $statement->fetchAll(\PDO::FETCH_COLUMN);
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
				$statement->execute([0]);
				$this->matches = $statement->fetchAll(\PDO::FETCH_COLUMN);
				break;
			
			case 'disabledAvatars':
				$sql = "SELECT		user_table.userID
					FROM		wcf".WCF_N."_user user_table
					LEFT JOIN	wcf".WCF_N."_user_option_value option_value
					ON		(option_value.userID = user_table.userID)
					WHERE		disableAvatar = ?";
				$statement = WCF::getDB()->prepareStatement($sql, $this->maxResults);
				$statement->execute([1]);
				$this->matches = $statement->fetchAll(\PDO::FETCH_COLUMN);
				break;
					
			case 'disabledSignatures':
				$sql = "SELECT		user_table.userID
					FROM		wcf".WCF_N."_user user_table
					LEFT JOIN	wcf".WCF_N."_user_option_value option_value
					ON		(option_value.userID = user_table.userID)
					WHERE		disableSignature = ?";
				$statement = WCF::getDB()->prepareStatement($sql, $this->maxResults);
				$statement->execute([1]);
				$this->matches = $statement->fetchAll(\PDO::FETCH_COLUMN);
				break;
		}
		
		if (empty($this->matches)) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.acp.user.search.error.noMatches'));
		}
		
		// store search result in database
		$data = serialize([
			'matches' => $this->matches,
			'itemsPerPage' => $this->itemsPerPage,
			'columns' => $this->columns
		]);
		
		$search = SearchEditor::create([
			'userID' => WCF::getUser()->userID,
			'searchData' => $data,
			'searchTime' => TIME_NOW,
			'searchType' => 'users'
		]);
		$this->executed();
		
		// forward to result page
		$url = LinkHandler::getInstance()->getLink('UserList', ['id' => $search->searchID], 'sortField='.rawurlencode($this->sortField).'&sortOrder='.rawurlencode($this->sortOrder));
		HeaderUtil::redirect($url);
		exit;
	}
}
