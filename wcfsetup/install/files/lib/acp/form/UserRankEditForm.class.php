<?php
namespace wcf\acp\form;
use wcf\data\user\rank\UserRank;
use wcf\data\user\rank\UserRankAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the user rank edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class UserRankEditForm extends UserRankAddForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.rank';
	
	/**
	 * rank id
	 * @var	integer
	 */
	public $rankID = 0;
	
	/**
	 * rank object
	 * @var	\wcf\data\user\rank\UserRank
	 */
	public $rank = null;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->rankID = intval($_REQUEST['id']);
		$this->rank = new UserRank($this->rankID);
		if (!$this->rank->rankID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		$this->rankTitle = 'wcf.user.rank.userRank'.$this->rank->rankID;
		if (I18nHandler::getInstance()->isPlainValue('rankTitle')) {
			I18nHandler::getInstance()->remove($this->rankTitle);
			$this->rankTitle = I18nHandler::getInstance()->getValue('rankTitle');
		}
		else {
			I18nHandler::getInstance()->save('rankTitle', $this->rankTitle, 'wcf.user', 1);
		}
		
		// update label
		$this->objectAction = new UserRankAction(array($this->rank), 'update', array('data' => array_merge($this->additionalFields, array(
			'rankTitle' => $this->rankTitle,
			'cssClassName' => ($this->cssClassName == 'custom' ? $this->customCssClassName : $this->cssClassName),
			'groupID' => $this->groupID,
			'requiredPoints' => $this->requiredPoints,
			'rankImage' => $this->rankImage,
			'repeatImage' => $this->repeatImage,
			'requiredGender' => $this->requiredGender
		))));
		$this->objectAction->executeAction();
		$this->saved();
		
		// reset values if non-custom value was choosen
		if ($this->cssClassName != 'custom') $this->customCssClassName = '';
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			I18nHandler::getInstance()->setOptions('rankTitle', 1, $this->rank->rankTitle, 'wcf.user.rank.userRank\d+');
			$this->rankTitle = $this->rank->rankTitle;
			$this->cssClassName = $this->rank->cssClassName;
			if (!in_array($this->cssClassName, $this->availableCssClassNames)) {
				$this->customCssClassName = $this->cssClassName;
				$this->cssClassName = 'custom';
			}
			$this->groupID = $this->rank->groupID;
			$this->requiredPoints = $this->rank->requiredPoints;
			$this->requiredGender = $this->rank->requiredGender;
			$this->repeatImage = $this->rank->repeatImage;
			$this->rankImage = $this->rank->rankImage;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign(array(
			'rankID' => $this->rankID,
			'rank' => $this->rank,
			'action' => 'edit'
		));
	}
}
