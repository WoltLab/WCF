<?php
namespace wcf\acp\form;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\Article;
use wcf\data\article\ArticleAction;
use wcf\data\category\CategoryNodeTree;
use wcf\data\language\Language;
use wcf\data\media\Media;
use wcf\data\media\ViewableMediaList;
use wcf\data\user\User;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\DateUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the article add form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.0
 */
class ArticleAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.article.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_ARTICLE'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.article.canManageArticle'];
	
	/**
	 * true if created article is multi-lingual
	 * @var	boolean
	 */
	public $isMultilingual = 0;
	
	/**
	 * category id
	 * @var integer
	 */
	public $categoryID = 0;
	
	/**
	 * author's username
	 * @var string
	 */
	public $username = '';
	
	/**
	 * author
	 * @var User
	 */
	public $author;
	
	/**
	 * article date (ISO 8601)
	 * @var	string
	 */
	public $time = '';
	
	/**
	 * article date object
	 * @var	\DateTime
	 */
	public $timeObj;
	
	/**
	 * publication status
	 * @var integer
	 */
	public $publicationStatus = Article::PUBLISHED;
	
	/**
	 * publication date (ISO 8601)
	 * @var	string
	 */
	public $publicationDate = '';
	
	/**
	 * publication date object
	 * @var	\DateTime
	 */
	public $publicationDateObj;
	
	/**
	 * enables the comment function
	 * @var	boolean
	 */
	public $enableComments = ARTICLE_ENABLE_COMMENTS_DEFAULT_VALUE;
	
	/**
	 * article titles
	 * @var	string[]
	 */
	public $title = [];
	
	/**
	 * tags
	 * @var	string[][]
	 */
	public $tags = [];
	
	/**
	 * article teasers
	 * @var	string[]
	 */
	public $teaser = [];
	
	/**
	 * article contents
	 * @var	string[]
	 */
	public $content = [];
	
	/**
	 * @var HtmlInputProcessor[]
	 */
	public $htmlInputProcessors = [];
	
	/**
	 * image ids
	 * @var	integer[]
	 */
	public $imageID = [];
	
	/**
	 * images
	 * @var	Media[]
	 */
	public $images = [];
	
	/**
	 * list of available languages
	 * @var	Language[]
	 */
	public $availableLanguages = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['categoryID'])) $this->categoryID = intval($_REQUEST['categoryID']);
		
		$this->readMultilingualSetting();
		
		// get available languages
		$this->availableLanguages = LanguageFactory::getInstance()->getLanguages();
	}
	
	/**
	 * Reads basic article parameters controlling i18n.
	 */
	protected function readMultilingualSetting() {
		if (!empty($_REQUEST['isMultilingual'])) $this->isMultilingual = 1;
		
		// work-around to force adding article via dialog overlay
		if (empty($_POST) && !isset($_REQUEST['isMultilingual'])) {
			$parameters = ['showArticleAddDialog' => 1];
			if ($this->categoryID) $parameters['categoryID'] = $this->categoryID;
			HeaderUtil::redirect(LinkHandler::getInstance()->getLink('ArticleList', $parameters));
			exit;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->enableComments = 0;
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['time'])) {
			$this->time = $_POST['time'];
			$this->timeObj = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $this->time);
		}
		if (!empty($_POST['enableComments'])) $this->enableComments = 1;
		if (isset($_POST['publicationStatus'])) $this->publicationStatus = intval($_POST['publicationStatus']);
		if ($this->publicationStatus == Article::DELAYED_PUBLICATION && isset($_POST['publicationDate'])) {
			$this->publicationDate = $_POST['publicationDate'];
			$this->publicationDateObj = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $this->publicationDate);
		}
		if (isset($_POST['title']) && is_array($_POST['title'])) $this->title = ArrayUtil::trim($_POST['title']);
		if (MODULE_TAGGING && isset($_POST['tags']) && is_array($_POST['tags'])) $this->tags = ArrayUtil::trim($_POST['tags']);
		if (isset($_POST['teaser']) && is_array($_POST['teaser'])) $this->teaser = ArrayUtil::trim($_POST['teaser']);
		if (isset($_POST['content']) && is_array($_POST['content'])) $this->content = ArrayUtil::trim($_POST['content']);
		
		if (WCF::getSession()->getPermission('admin.content.cms.canUseMedia')) {
			if (isset($_POST['imageID']) && is_array($_POST['imageID'])) $this->imageID = ArrayUtil::toIntegerArray($_POST['imageID']);
			
			$this->readImages();
		}
	}
	
	/**
	 * Reads the box images.
	 */
	protected function readImages() {
		if (!empty($this->imageID)) {
			$mediaList = new ViewableMediaList();
			$mediaList->setObjectIDs($this->imageID);
			$mediaList->readObjects();
			
			foreach ($this->imageID as $languageID => $imageID) {
				$image = $mediaList->search($imageID);
				if ($image !== null && $image->isImage) {
					$this->images[$languageID] = $image;
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// category
		if (empty($this->categoryID)) {
			throw new UserInputException('categoryID');
		}
		$category = ArticleCategory::getCategory($this->categoryID);
		if ($category === null) {
			throw new UserInputException('categoryID', 'invalid');
		}
		
		// author
		if (empty($this->username)) {
			throw new UserInputException('username');
		}
		$this->author = User::getUserByUsername($this->username);
		if (!$this->author->userID) {
			throw new UserInputException('username', 'notFound');
		}
		
		// article date
		if (empty($this->time)) {
			throw new UserInputException('time');
		}
		if (!$this->timeObj) {
			throw new UserInputException('time', 'invalid');
		}
		
		// publication status
		if ($this->publicationStatus != Article::UNPUBLISHED && $this->publicationStatus != Article::PUBLISHED && $this->publicationStatus != Article::DELAYED_PUBLICATION) {
			throw new UserInputException('publicationStatus');
		}
		if ($this->publicationStatus == Article::DELAYED_PUBLICATION) {
			if (empty($this->publicationDate)) {
				throw new UserInputException('publicationDate');
			}
			
			if (!$this->publicationDateObj || $this->publicationDateObj->getTimestamp() < TIME_NOW) {
				throw new UserInputException('publicationDate', 'invalid');
			}
		}
		
		if ($this->isMultilingual) {
			foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
				// title
				if (empty($this->title[$language->languageID])) {
					throw new UserInputException('title'.$language->languageID);
				}
				// content
				if (empty($this->content[$language->languageID])) {
					throw new UserInputException('content'.$language->languageID);
				}
				
				$this->htmlInputProcessors[$language->languageID] = new HtmlInputProcessor();
				$this->htmlInputProcessors[$language->languageID]->process($this->content[$language->languageID], 'com.woltlab.wcf.article.content', 0);
			}
		}
		else {
			// title
			if (empty($this->title[0])) {
				throw new UserInputException('title');
			}
			// content
			if (empty($this->content[0])) {
				throw new UserInputException('content');
			}
			
			$this->htmlInputProcessors[0] = new HtmlInputProcessor();
			$this->htmlInputProcessors[0]->process($this->content[0], 'com.woltlab.wcf.article.content', 0);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$content = [];
		if ($this->isMultilingual) {
			foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
				$content[$language->languageID] = [
					'title' => (!empty($this->title[$language->languageID]) ? $this->title[$language->languageID] : ''),
					'tags' => (!empty($this->tags[$language->languageID]) ? $this->tags[$language->languageID] : []),
					'teaser' => (!empty($this->teaser[$language->languageID]) ? $this->teaser[$language->languageID] : ''),
					'content' => (!empty($this->content[$language->languageID]) ? $this->content[$language->languageID] : ''),
					'htmlInputProcessor' => (isset($this->htmlInputProcessors[$language->languageID]) ? $this->htmlInputProcessors[$language->languageID] : null),
					'imageID' => (!empty($this->imageID[$language->languageID]) ? $this->imageID[$language->languageID] : null)
				];
			}
		}
		else {
			$content[0] = [
				'title' => (!empty($this->title[0]) ? $this->title[0] : ''),
				'tags' => (!empty($this->tags[0]) ? $this->tags[0] : []),
				'teaser' => (!empty($this->teaser[0]) ? $this->teaser[0] : ''),
				'content' => (!empty($this->content[0]) ? $this->content[0] : ''),
				'htmlInputProcessor' => (isset($this->htmlInputProcessors[0]) ? $this->htmlInputProcessors[0] : null),
				'imageID' => (!empty($this->imageID[0]) ? $this->imageID[0] : null)
			];
		}
		
		$data = [
			'time' => $this->timeObj->getTimestamp(),
			'categoryID' => $this->categoryID,
			'publicationStatus' => $this->publicationStatus,
			'publicationDate' => ($this->publicationStatus == Article::DELAYED_PUBLICATION ? $this->publicationDateObj->getTimestamp() : 0),
			'enableComments' => $this->enableComments,
			'userID' => $this->author->userID,
			'username' => $this->author->username,
			'isMultilingual' => $this->isMultilingual
		];
		
		$this->objectAction = new ArticleAction([], 'create', ['data' => array_merge($this->additionalFields, $data), 'content' => $content]);
		$this->objectAction->executeAction();
		
		// call saved event
		$this->saved();
		
		// show success
		WCF::getTPL()->assign('success', true);
		
		// reset variables
		$this->publicationDate = '';
		$this->categoryID = 0;
		$this->publicationStatus = Article::PUBLISHED;
		$this->enableComments = ARTICLE_ENABLE_COMMENTS_DEFAULT_VALUE;
		$this->title = $this->teaser = $this->content = $this->images = $this->imageID = $this->tags = [];
		
		$this->setDefaultValues();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->setDefaultValues();
		}
	}
	
	/**
	 * Sets the default values of properties.
	 */
	protected function setDefaultValues() {
		$this->username = WCF::getUser()->username;
		$dateTime = DateUtil::getDateTimeByTimestamp(TIME_NOW);
		$dateTime->setTimezone(WCF::getUser()->getTimeZone());
		$this->time = $dateTime->format('c');
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'add',
			'isMultilingual' => $this->isMultilingual,
			'categoryID' => $this->categoryID,
			'username' => $this->username,
			'time' => $this->time,
			'enableComments' => $this->enableComments,
			'publicationStatus' => $this->publicationStatus,
			'publicationDate' => $this->publicationDate,
			'imageID' => $this->imageID,
			'images' => $this->images,
			'tags' => $this->tags,
			'title' => $this->title,
			'teaser' => $this->teaser,
			'content' => $this->content,
			'availableLanguages' => $this->availableLanguages,
			'categoryNodeList' => (new CategoryNodeTree('com.woltlab.wcf.article.category'))->getIterator()
		]);
	}
}
