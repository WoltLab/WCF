<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\system\user\UserBirthdayCache;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * Condition implementation if it is the active user uses a mobile browser.
 * 
 * @author	Matthias Schmidt
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
	 * @see	\wcf\system\condition\ICondition::getData()
	 */
	public function getData() {
		if ($this->usesMobileBrowser) {
			return array(
				'usesMobileBrowser' => 1
			);
		}
		
		return null;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::getHTML()
	 */
	public function getHTML() {
		$label = WCF::getLanguage()->get('wcf.user.condition.usesMobileBrowser');
		$checked = '';
		if ($this->usesMobileBrowser) {
			$checked = ' checked="checked"';
		}
		
		return <<<HTML
<dl>
	<dt></dt>
	<dd>
		<label><input type="checkbox" name="usesMobileBrowser" id="usesMobileBrowser"{$checked} /> {$label}</label>
	</dd>
</dl>
HTML;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::readFormParameters()
	 */
	public function readFormParameters() {
		if (isset($_POST['usesMobileBrowser'])) $this->usesMobileBrowser = 1;
	}
	
	/**
	 * @see	\wcf\system\condition\ICondition::reset()
	 */
	public function reset() {
		$this->usesMobileBrowser = 0;
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
		return UserUtil::usesMobileBrowser();
	}
}
