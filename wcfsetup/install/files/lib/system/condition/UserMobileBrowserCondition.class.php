<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * Condition implementation if it is the active user uses a mobile browser.
 * 
 * @author	Matthias Schmidt, Joshua Rüsweg
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
class UserMobileBrowserCondition extends AbstractCondition implements IContentCondition {
	/**
	 * 1 if mobile browser checkbox is checked
	 * @var	integer
	 */
	protected $usesMobileBrowser = 0;
	
	/**
	 * 1 if not use mobile browser checkbox is checked
	 * @var	integer
	 */
	protected $notUseMobileBrowser = 0;
	
	/**
	 * @see	\wcf\system\condition\ICondition::getData()
	 */
	public function getData() {
		if ($this->usesMobileBrowser || $this->notUseMobileBrowser) {
			return array(
				// if notUseMobileBrowser is selected usesMobileBrowser is 0
				// otherwise notUseMobileBrowser is 1
				// if both is selected "usesMobileBrowser" is the strong parameter
				'usesMobileBrowser' => $this->usesMobileBrowser
			);
		}
		
		return null;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::getHTML()
	 */
	public function getHTML() {
		$usesMobileBrowserLabel = WCF::getLanguage()->get('wcf.user.condition.usesMobileBrowser');
		$notUseMobileBrowserLabel = WCF::getLanguage()->get('wcf.user.condition.notUseMobileBrowser');
		$usesMobileBrowserChecked = '';
		if ($this->usesMobileBrowser) {
			$usesMobileBrowserChecked = ' checked="checked"';
		}
		
		$notUseMobileBrowserChecked = '';
		if ($this->notUseMobileBrowser) {
			$notUseMobileBrowserChecked = ' checked="checked"';
		}
		
		return <<<HTML
<dl>
	<dt></dt>
	<dd>
		<label><input type="checkbox" name="usesMobileBrowser" id="usesMobileBrowser"{$usesMobileBrowserChecked} /> {$usesMobileBrowserLabel}</label>
		<label><input type="checkbox" name="notUseMobileBrowser" id="notUseMobileBrowser"{$notUseMobileBrowserChecked} /> {$notUseMobileBrowserLabel}</label>
	</dd>
</dl>
HTML;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::readFormParameters()
	 */
	public function readFormParameters() {
		if (isset($_POST['usesMobileBrowser'])) $this->usesMobileBrowser = 1;
		if (isset($_POST['notUseMobileBrowser'])) $this->notUseMobileBrowser = 1;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::reset()
	 */
	public function reset() {
		$this->usesMobileBrowser = 0;
		$this->notUseMobileBrowser = 0; 
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::readFormParameters()
	 */
	public function setData(Condition $condition) {
		$this->usesMobileBrowser = $condition->usesMobileBrowser;
	}
	
	/**
	 * @see	\wcf\system\condition\IContentCondition::showContent()
	 */
	public function showContent(Condition $condition) {
		return (($condition->usesMobileBrowser && UserUtil::usesMobileBrowser()) || (!$condition->usesMobileBrowser && !UserUtil::usesMobileBrowser()));
	}
}