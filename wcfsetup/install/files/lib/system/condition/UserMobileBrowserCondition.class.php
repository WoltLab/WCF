<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * Condition implementation if it is the active user uses a mobile browser.
 * 
 * @author	Joshua Ruesweg, Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
class UserMobileBrowserCondition extends AbstractSingleFieldCondition implements IContentCondition {
	/**
	 * @inheritDoc
	 */
	protected $label = 'wcf.user.condition.mobileBrowser';
	
	/**
	 * 1 if mobile browser checkbox is checked
	 * @var	integer
	 */
	protected $usesMobileBrowser = 0;
	
	/**
	 * 1 if not use mobile browser checkbox is checked
	 * @var	integer
	 */
	protected $usesNoMobileBrowser = 0;
	
	/**
	 * @inheritDoc
	 */
	public function getData() {
		if ($this->usesMobileBrowser || $this->usesNoMobileBrowser) {
			return [
				// if notUseMobileBrowser is selected usesMobileBrowser is 0
				// otherwise notUseMobileBrowser is 1
				'usesMobileBrowser' => $this->usesMobileBrowser
			];
		}
		
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFieldElement() {
		$usesMobileBrowserLabel = WCF::getLanguage()->get('wcf.user.condition.mobileBrowser.usesMobileBrowser');
		$usesNoMobileBrowserLabel = WCF::getLanguage()->get('wcf.user.condition.mobileBrowser.usesNoMobileBrowser');
		$usesMobileBrowserChecked = '';
		if ($this->usesMobileBrowser) {
			$usesMobileBrowserChecked = ' checked="checked"';
		}
		
		$usesNoMobileBrowserChecked = '';
		if ($this->usesNoMobileBrowser) {
			$usesNoMobileBrowserChecked = ' checked="checked"';
		}
		
		return <<<HTML
<label><input type="checkbox" name="usesMobileBrowser" id="usesMobileBrowser"{$usesMobileBrowserChecked} /> {$usesMobileBrowserLabel}</label>
<label><input type="checkbox" name="usesNoMobileBrowser" id="usesNoMobileBrowser"{$usesNoMobileBrowserChecked} /> {$usesNoMobileBrowserLabel}</label>
HTML;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		if (isset($_POST['usesMobileBrowser'])) $this->usesMobileBrowser = 1;
		if (isset($_POST['usesNoMobileBrowser'])) $this->usesNoMobileBrowser = 1;
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset() {
		$this->usesMobileBrowser = 0;
		$this->usesNoMobileBrowser = 0; 
	}
	
	/**
	 * @inheritDoc
	 */
	public function setData(Condition $condition) {
		$this->usesMobileBrowser = $condition->usesMobileBrowser;
		$this->usesNoMobileBrowser = !$condition->usesMobileBrowser; 
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if ($this->usesMobileBrowser && $this->usesNoMobileBrowser) {
			$this->errorMessage = 'wcf.user.condition.mobileBrowser.usesMobileBrowser.error.conflict';
			
			throw new UserInputException('mobileBrowser', 'conflict');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function showContent(Condition $condition) {
		return (($condition->usesMobileBrowser && UserUtil::usesMobileBrowser()) || (!$condition->usesMobileBrowser && !UserUtil::usesMobileBrowser()));
	}
}
