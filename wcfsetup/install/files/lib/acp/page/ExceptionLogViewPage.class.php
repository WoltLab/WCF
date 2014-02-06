<?php
namespace wcf\acp\page;
use wcf\page\AbstractPage;
use wcf\page\MultipleLinkPage;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Shows the exception log.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class ExceptionLogViewPage extends MultipleLinkPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.log.exception';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.canViewLog');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$itemsPerPage
	 */
	public $itemsPerPage = 10;
	
	/**
	 * given exceptionID
	 * @var	string
	 */
	public $exceptionID = '';
	
	/**
	 * active logfile
	 * @var	string
	 */
	public $logFile = '';
	
	/**
	 * available logfiles
	 * @var	array<string>
	 */
	public $logFiles = array();
	
	/**
	 * exceptions shown
	 * @var	array
	 */
	public $exceptions = array();
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['exceptionID'])) $this->exceptionID = StringUtil::trim($_REQUEST['exceptionID']);
		if (isset($_REQUEST['logFile'])) $this->logFile = StringUtil::trim($_REQUEST['logFile']);
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		AbstractPage::readData();
		
		$fileNameRegex = new Regex('\d{4}-\d{2}-\d{2}\.txt$');
		$this->logFiles = DirectoryUtil::getInstance(WCF_DIR.'log/')->getFiles(SORT_DESC, $fileNameRegex);
		
		if ($this->exceptionID) {
			// search the appropriate file
			foreach ($this->logFiles as $logFile) {
				$contents = file_get_contents($logFile);
				
				if (mb_strpos($contents, '<<<<<<<<'.$this->exceptionID.'<<<<') !== false) {
					$fileNameRegex->match($logFile);
					$matches = $fileNameRegex->getMatches();
					$this->logFile = $matches[0];
					break;
				}
				
				unset($contents);
			}
			
			if (!isset($contents)) {
				$this->logFile = '';
				return;
			}
		}
		else if ($this->logFile) {
			if (!$fileNameRegex->match(basename($this->logFile))) throw new IllegalLinkException();
			if (!file_exists(WCF_DIR.'log/'.$this->logFile)) throw new IllegalLinkException();
			
			$contents = file_get_contents(WCF_DIR.'log/'.$this->logFile);
		}
		else {
			return;
		}
		
		// unify newlines
		$contents = StringUtil::unifyNewlines($contents);
		
		// split contents
		$split = new Regex('(?:^|\n<<<<\n\n)(?:<<<<<<<<([a-f0-9]{40})<<<<\n|$)');
		$contents = $split->split($contents, Regex::SPLIT_NON_EMPTY_ONLY | Regex::CAPTURE_SPLIT_DELIMITER);
		
		// even items become keys, odd items become values
		$this->exceptions = call_user_func_array('array_merge', array_map(
			function($v) { 
				return array($v[0] => $v[1]); 
			}, 
			array_chunk($contents, 2)
		));
		
		if ($this->exceptionID) $this->searchPage($this->exceptionID);
		$this->calculateNumberOfPages();
		
		$i = 0;
		$exceptionRegex = new Regex('(?P<date>[MTWFS][a-z]{2}, \d{1,2} [JFMASOND][a-z]{2} \d{4} \d{2}:\d{2}:\d{2} [+-]\d{4})
Message: (?P<message>.*?)
File: (?P<file>.*?) \((?P<line>\d+)\)
PHP version: (?P<phpVersion>.*?)
WCF version: (?P<wcfVersion>.*?)
Request URI: (?P<requestURI>.*?)
Referrer: (?P<referrer>.*?)
User-Agent: (?P<userAgent>.*?)
Information: (?P<information>.*?)
Stacktrace: 
(?P<stacktrace>.*)', Regex::DOT_ALL);
		$stackTraceFormatter = new Regex('^\s+(#\d+)', Regex::MULTILINE);
		foreach ($this->exceptions as $key => $val) {
			$i++;
			if ($i < $this->startIndex || $i > $this->endIndex) {
				unset($this->exceptions[$key]);
				continue;
			}
			
			if (!$exceptionRegex->match($val)) {
				unset($this->exceptions[$key]);
				continue;
			}
			
			$this->exceptions[$key] = $exceptionRegex->getMatches();
			$this->exceptions[$key]['stacktrace'] = explode("\n", $stackTraceFormatter->replace(StringUtil::encodeHTML($this->exceptions[$key]['stacktrace']), '<strong>\\1</strong>'));
			$this->exceptions[$key]['information'] = JSON::decode($this->exceptions[$key]['information']);
		}
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::countItems()
	 */
	public function countItems() {
		// call countItems event
		EventHandler::getInstance()->fireAction($this, 'countItems');
		
		return count($this->exceptions);
	}
	
	/**
	 * Switches to the page containing the exception with the given ID.
	 * 
	 * @param	string	$exceptionID
	 */
	public function searchPage($exceptionID) {
		$i = 1;
		
		foreach ($this->exceptions as $key => $val) {
			if ($key == $exceptionID) break;
			$i++;
		}
		
		$this->pageNo = ceil($i / $this->itemsPerPage);
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'exceptionID' => $this->exceptionID,
			'logFiles' => array_flip(array_map('basename', $this->logFiles)),
			'logFile' => $this->logFile,
			'exceptions' => $this->exceptions
		));
	}
}
