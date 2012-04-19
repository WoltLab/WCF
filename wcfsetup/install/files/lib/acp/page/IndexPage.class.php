<?php
namespace wcf\acp\page;
use wcf\page\AbstractPage;
use wcf\system\cache\CacheHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;
use wcf\system\WCFACP;

/**
 * Shows the welcome page in admin control panel.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class IndexPage extends AbstractPage {
	/**
	 * @see wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'index';
	
	/**
	 * Did you know language item.
	 *
	 * @var string
	 */
	public $didYouKnow = '';
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'didYouKnow' => $this->didYouKnow
		));
	}
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$sql = "SELECT
				languageItem
			FROM
				wcf".WCF_N."_language_item
			WHERE
				languageCategoryID = ?
			ORDER BY
				rand()";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		// TODO: Change category
		$statement->execute(array(LanguageFactory::getInstance()->getCategory('wcf.global')->languageCategoryID));
		$this->didYouKnow = $statement->fetchColumn();
	}
	
	/**
	 * @see wcf\page\IPage::show()
	 */
	public function show() {
		$wcfPackageID = WCFACP::getWcfPackageID();
		// check package installation queue
		if ($wcfPackageID == 0) {
			$queueID = PackageInstallationDispatcher::checkPackageInstallationQueue();
			
			if ($queueID) {
				WCF::getTPL()->assign(array(
					'queueID' => $queueID
				));
				WCF::getTPL()->display('packageInstallationSetup');
				exit;
			}
		}
		
		// show page
		parent::show();
	}
}
