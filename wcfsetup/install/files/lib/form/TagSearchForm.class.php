<?php
namespace wcf\form;
use wcf\data\language\Language;
use wcf\data\tag\Tag;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\tagging\TagCloud;
use wcf\system\tagging\TagEngine;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\HeaderUtil;

/**
 * Shows the tag search form.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Form
 * @since       3.2
 */
class TagSearchForm extends AbstractCaptchaForm {
	/**
	 * @var Language[]
	 */
	public $availableContentLanguages = [];
	
	/**
	 * @var int
	 */
	public $languageID;
	
	/**
	 * @var TagCloud
	 */
	public $tagCloud;
	
	/**
	 * @var string[]
	 */
	public $tagNames;
	
	/**
	 * @var Tag[]
	 */
	public $tags;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->availableContentLanguages = LanguageFactory::getInstance()->getContentLanguages();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['languageID'])) $this->languageID = intval($_POST['languageID']);
		if (isset($_POST['tagNames']) && is_array($_POST['tagNames'])) $this->tagNames = ArrayUtil::trim($_POST['tagNames']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if ($this->languageID !== null) {
			if (!in_array($this->languageID, LanguageFactory::getInstance()->getContentLanguageIDs())) {
				throw new UserInputException('languageID');
			}
		}
		
		$this->tags = TagEngine::getInstance()->getTagsByName($this->tagNames, $this->languageID);
		if (empty($this->tags)) {
			throw new UserInputException('tags');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		if (empty($_POST)) {
			$this->languageID = WCF::getLanguage()->languageID;
		}
		
		parent::readData();
		
		$this->tagCloud = new TagCloud();
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		HeaderUtil::redirect(
			LinkHandler::getInstance()->getLink('CombinedTagged', ['tagIDs' => array_keys($this->tags)]),
			true,
			true
		);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'availableContentLanguages' => $this->availableContentLanguages,
			'languageID' => $this->languageID ?: 0,
			'tags' => $this->tagCloud->getTags(),
			'tagNames' => $this->tagNames,
		]);
	}
}
