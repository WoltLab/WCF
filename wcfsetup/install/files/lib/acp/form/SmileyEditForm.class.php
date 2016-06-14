<?php
namespace wcf\acp\form;
use wcf\data\smiley\Smiley;
use wcf\data\smiley\SmileyAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the smiley edit form.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class SmileyEditForm extends SmileyAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.smiley';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.smiley.canManageSmiley'];
	
	/**
	 * smiley id
	 * @var	integer
	 */
	public $smileyID = 0;
	
	/**
	 * smiley object
	 * @var	\wcf\data\smiley\Smiley
	 */
	public $smiley = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->smileyID = intval($_REQUEST['id']);
		$this->smiley = new Smiley($this->smileyID);
		if (!$this->smiley->smileyID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		$this->smileyTitle = 'wcf.smiley.title'.$this->smiley->smileyID;
		if (I18nHandler::getInstance()->isPlainValue('smileyTitle')) {
			I18nHandler::getInstance()->remove($this->smileyTitle);
			$this->smileyTitle = I18nHandler::getInstance()->getValue('smileyTitle');
		}
		else {
			I18nHandler::getInstance()->save('smileyTitle', $this->smileyTitle, 'wcf.smiley', 1);
		}
		
		// update bbcode
		$this->objectAction = new SmileyAction([$this->smileyID], 'update', [
			'data' => array_merge($this->additionalFields, [
				'smileyTitle' => $this->smileyTitle,
				'smileyCode' => $this->smileyCode,
				'aliases' => $this->aliases,
				'smileyPath' => $this->smileyPath,
				'showOrder' => $this->showOrder,
				'categoryID' => $this->categoryID ?: null
			]),
			'fileLocation' => $this->uploadedFilename ? WCF_DIR.'images/smilies/'.$this->uploadedFilename : ''
		]);
		$this->objectAction->executeAction();
		
		$this->uploadedFilename = '';
		
		$this->saved();
		
		// show success
		WCF::getTPL()->assign([
			'success' => true
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			I18nHandler::getInstance()->setOptions('smileyTitle', 1, $this->smiley->smileyTitle, 'wcf.smiley.title\d+');
			$this->smileyTitle = $this->smiley->smileyTitle;
			
			$this->smileyCode = $this->smiley->smileyCode;
			$this->aliases = $this->smiley->aliases;
			$this->smileyPath = $this->smiley->smileyPath;
			$this->showOrder = $this->smiley->showOrder;
			$this->categoryID = $this->smiley->categoryID;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign([
			'smiley' => $this->smiley,
			'action' => 'edit'
		]);
	}
}
