<?php
namespace wcf\acp\form;
use wcf\data\media\MediaAction;
use wcf\data\media\ViewableMedia;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Shows the form to edit a media file.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.0
 */
class MediaEditForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.media.list';
	
	/**
	 * is 1 if media data is multilingual
	 * @var	integer
	 */
	public $isMultilingual = 0;
	
	/**
	 * id of the selected language
	 * @var	integer
	 */
	public $languageID = 0;
	
	/**
	 * edited media
	 * @var	ViewableMedia
	 */
	public $media = null;
	
	/**
	 * id of the edited media
	 * @var	integer
	 */
	public $mediaID = 0;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.cms.canManageMedia'];
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'edit',
			'isMultilingual' => $this->isMultilingual,
			'languages' => LanguageFactory::getInstance()->getLanguages(),
			'languageID' => $this->languageID,
			'media' => $this->media
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->isMultilingual = $this->media->isMultilingual;
			if (!$this->isMultilingual && !$this->media->languageID) {
				$this->isMultilingual = 1;
			}
			
			if ($this->media->languageID) {
				$this->languageID = $this->media->languageID;
			}
			else {
				$this->languageID = WCF::getUser()->languageID;
			}
			
			$contentData = $this->media->getI18nData();
			if (!empty($contentData)) {
				if (!empty($contentData['altText'])) I18nHandler::getInstance()->setValues('altText', $contentData['altText']);
				if (!empty($contentData['caption'])) I18nHandler::getInstance()->setValues('caption', $contentData['caption']);
				if (!empty($contentData['title'])) I18nHandler::getInstance()->setValues('title', $contentData['title']);
			}
		}
		
		if (!$this->languageID) {
			$this->languageID = WCF::getUser()->languageID;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['isMultilingual'])) $this->isMultilingual = intval($_POST['isMultilingual']);
		if (!$this->isMultilingual) {
			if (isset($_POST['languageID'])) $this->languageID = intval($_POST['languageID']);
		}
		I18nHandler::getInstance()->readValues();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->mediaID = intval($_REQUEST['id']);
		
		$this->media = ViewableMedia::getMedia($this->mediaID);
		if ($this->media === null) {
			throw new IllegalLinkException();
		}
		
		I18nHandler::getInstance()->register('title');
		I18nHandler::getInstance()->register('caption');
		I18nHandler::getInstance()->register('altText');
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new MediaAction([$this->media->getDecoratedObject()], 'update', array_merge($this->additionalFields, [
			'data' => [
				'isMultilingual' => $this->isMultilingual,
				'languageID' => $this->languageID ?: null
			],
			'altText' => I18nHandler::getInstance()->getValues('altText'),
			'caption' => I18nHandler::getInstance()->getValues('caption'),
			'title' => I18nHandler::getInstance()->getValues('title')
		]));
		$this->objectAction->executeAction();
		
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 * @throws	UserInputException
	 */
	public function validate() {
		parent::validate();
		
		if (!$this->isMultilingual && !$this->languageID) {
			throw new UserInputException('languageID');
		}
		
		if ($this->languageID && !LanguageFactory::getInstance()->getLanguage($this->languageID)) {
			throw new UserInputException('languageID');
		}
		
		foreach (['title', 'caption', 'altText'] as $i18nData) {
			if (!I18nHandler::getInstance()->validateValue($i18nData, $this->isMultilingual? true : false, true)) {
				if ($this->isMultilingual) {
					// in contrast to I18nHandler::validateValues(), we allow all fields to be empty
					if (empty(ArrayUtil::trim(I18nHandler::getInstance()->getValues($i18nData)))) {
						continue;
					}
					
					throw new UserInputException($i18nData, 'multilingual');
				}
				else {
					throw new UserInputException($i18nData);
				}
			}
		}
	}
}
