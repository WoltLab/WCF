<?php
namespace wcf\page;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Safely redirect to an external url
 * 
 * @author	Sascha Greuel
 * @copyright	2013 Sascha Greuel
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
class DereferrerPage extends AbstractPage {
	const AVAILABLE_DURING_OFFLINE_MODE = true;
		
	/**
	 * @see	wcf\page\AbstractPage::$useTemplate
	 */
	public $useTemplate = false;
	
	/**
	 * url
	 * @var	string
	 */
	public $url = '';
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['url'])) $this->url = urldecode($_REQUEST['url']);
		
		$scheme = @parse_url($this->url, PHP_URL_SCHEME);
		
		if (empty($this->url) || !$scheme) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	wcf\action\IAction::execute()
	 */
	public function show() {
		parent::show();
		
		HeaderUtil::delayedRedirect($this->url, '', 0);
	}
}
