<?php
namespace wcf\acp\page;
use wcf\page\AbstractPage;
use wcf\page\MultipleLinkPage;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\StringUtil;

/**
 * Shows the exception log.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 */
class ExceptionLogViewPage extends MultipleLinkPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.log.exception';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.management.canViewLog'];
	
	/**
	 * @inheritDoc
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
	 * @var	string[]
	 */
	public $logFiles = [];
	
	/**
	 * exceptions shown
	 * @var	array
	 */
	public $exceptions = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['exceptionID'])) $this->exceptionID = StringUtil::trim($_REQUEST['exceptionID']);
		if (isset($_REQUEST['logFile'])) $this->logFile = StringUtil::trim($_REQUEST['logFile']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		AbstractPage::readData();
		
		$fileNameRegex = new Regex('(?:^|/)\d{4}-\d{2}-\d{2}\.txt$');
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
		try {
			$this->exceptions = call_user_func_array('array_merge', array_map(
				function($v) {
					return [$v[0] => $v[1]];
				},
				array_chunk($contents, 2)
			));
		}
		catch (\Exception $e) {
			// logfile contents are pretty malformed, abort
			return;
		}
		
		if ($this->exceptionID) $this->searchPage($this->exceptionID);
		$this->calculateNumberOfPages();
		
		$i = 0;
		$exceptionRegex = new Regex("(?P<date>[MTWFS][a-z]{2}, \d{1,2} [JFMASOND][a-z]{2} \d{4} \d{2}:\d{2}:\d{2} [+-]\d{4})\s*\n".
"Message: (?P<message>.*?)\s*\n".
"PHP version: (?P<phpVersion>.*?)\s*\n".
"WCF version: (?P<wcfVersion>.*?)\s*\n".
"Request URI: (?P<requestURI>.*?)\s*\n".
"Referrer: (?P<referrer>.*?)\s*\n".
"User Agent: (?P<userAgent>.*?)\s*\n".
"Peak Memory Usage: (?<peakMemory>\d+)/(?<maxMemory>\d+)\s*\n".
"(?<chain>======\n".
".*)", Regex::DOT_ALL);
		$chainRegex = new Regex("======\n".
"Error Class: (?P<class>.*?)\s*\n".
"Error Message: (?P<message>.*?)\s*\n".
"Error Code: (?P<code>\d+)\s*\n".
"File: (?P<file>.*?) \((?P<line>\d+)\)\s*\n".
"Extra Information: (?P<information>(?:-|[a-zA-Z0-9+/]+={0,2}))\s*\n".
"Stack Trace: (?P<stack>[a-zA-Z0-9+/]+={0,2})", Regex::DOT_ALL);
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
			$matches = $exceptionRegex->getMatches();
			$chainRegex->match($matches['chain'], true, Regex::ORDER_MATCH_BY_SET);
			
			$chainMatches = array_map(function ($item) {
				if ($item['information'] === '-') $item['information'] = null;
				else $item['information'] = @unserialize(base64_decode($item['information']));
				
				$item['stack'] = @unserialize(base64_decode($item['stack']));
				
				return $item;
			}, $chainRegex->getMatches());
			
			$matches['chain'] = $chainMatches;
			$this->exceptions[$key] = $matches;
		}
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'exceptionID' => $this->exceptionID,
			'logFiles' => array_flip(array_map('basename', $this->logFiles)),
			'logFile' => $this->logFile,
			'exceptions' => $this->exceptions
		]);
	}
}
