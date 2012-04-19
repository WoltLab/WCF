<?php
namespace wcf\acp\page;
use wcf\page\AbstractPage;
use wcf\system\cache\CacheHandler;
use wcf\system\event\EventHandler;
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
	 * Detailed Health-Status
	 * 
	 * @var array
	 */
	public $healthDetails = array('error' => array(), 'warning' => array(), 'info' => array());
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$health = 'success';
		if (!empty($this->healthDetails['error'])) $health = 'error';
		else if (!empty($this->healthDetails['warning'])) $health = 'warning';
		else if (!empty($this->healthDetails['info'])) $health = 'info';
		
		WCF::getTPL()->assign(array(
			'didYouKnow' => $this->didYouKnow,
			'health' => $health,
			'healthDetails' => $this->healthDetails
		));
	}
	
	/**
	 * Performs various health checks
	 */
	public function calculateHealth() {
		try {
			// TODO: Fill this list
			$shouldBeWritable = array(WCF_DIR);
			foreach ($shouldBeWritable as $file) {
				if (!is_writable($file)) $this->healthDetails['warning'][] = WCF::getLanguage()->getDynamicVariable('wcf.acp.index.health.notWritable', array('file' => $file));
			}
			
			for($i = 0; $i < 7; $i++) {
				if (file_exists(WCF_DIR.'log/'.date('Y-m-d', TIME_NOW - 86400 * $i).'.txt')) {
					$this->healthDetails['error'][] = WCF::getLanguage()->getDynamicVariable('wcf.acp.index.health.exception', array('date' => TIME_NOW - 86400 * $i));
					break;
				}
			}
			
			EventHandler::getInstance()->fireAction($this, 'calculateHealth');
		}
		catch (\Exception $e) {
			$this->healthDetails['error'][] = $e->getMessage();
		}
	}
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->calculateHealth();
		
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
