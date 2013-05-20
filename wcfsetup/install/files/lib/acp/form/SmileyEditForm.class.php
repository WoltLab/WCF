<?php
namespace wcf\acp\form;
use wcf\data\package\PackageCache;
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
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.bbcode
 * @subpackage	acp.form
 * @category	Community Framework
 */
class SmileyEditForm extends SmileyAddForm {
	/**
	 * @see	wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.smiley';
	
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.content.smiley.canManageSmiley');
	
	/**
	 * smiley id
	 * @var	integer
	 */
	public $smileyID = 0;
	
	/**
	 * smiley object
	 * @var	wcf\data\smiley\Smiley
	 */
	public $smiley = null;
	
	/**
	 * @see	wcf\page\IPage::readParameters()
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
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		$this->smileyTitle = 'wcf.smiley.title'.$this->smiley->smileyID;
		if (I18nHandler::getInstance()->isPlainValue('smileyTitle')) {
			I18nHandler::getInstance()->remove($this->smileyTitle);
			$this->smileyTitle = I18nHandler::getInstance()->getValue('smileyTitle');
		}
		else {
			I18nHandler::getInstance()->save('smileyTitle', $this->smileyTitle, 'wcf.smiley', PackageCache::getInstance()->getPackageID('com.woltlab.wcf.bbcode'));
		}
		
		// update bbcode
		$this->objectAction = new SmileyAction(array($this->smileyID), 'update', array('data' => array(
			'smileyTitle' => $this->smileyTitle,
			'smileyCode' => $this->smileyCode,
			'showOrder' => $this->showOrder,
			'categoryID' => $this->categoryID ?: null,
			'aliases' => $this->aliases
		)));
		$this->objectAction->executeAction();
		
		$this->saved();
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			I18nHandler::getInstance()->setOptions('smileyTitle', 1, $this->smiley->smileyTitle, 'wcf.smiley.title\d+');
			$this->smileyTitle = $this->smiley->smileyTitle;
			
			$this->smileyCode = $this->smiley->smileyCode;
			$this->aliases = $this->smiley->aliases;
			$this->showOrder = $this->smiley->showOrder;
			$this->categoryID = $this->smiley->categoryID;
		}
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign(array(
			'smiley' => $this->smiley,
			'action' => 'edit'
		));
	}
}
