<?php
namespace wcf\page;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Generates RSS 2-Feeds.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
abstract class AbstractFeedPage extends AbstractAuthedPage {
	/**
	 * @inheritDoc
	 */
	public $templateName = 'rssFeed';
	
	/**
	 * application name
	 * @var	string
	 */
	public $application = 'wcf';
	
	/**
	 * @inheritDoc
	 */
	public $useTemplate = false;
	
	/**
	 * parsed contents of $_REQUEST['id']
	 * @var	integer[]
	 */
	public $objectIDs = [];
	
	/**
	 * list of feed-entries for the current page
	 * @var	\wcf\data\DatabaseObjectList
	 */
	public $items = null;
	
	/**
	 * feed title
	 * @var	string
	 */
	public $title = '';
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'items' => $this->items,
			'title' => $this->title
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) {
			if (is_array($_REQUEST['id'])) {
				// ?id[]=1337&id[]=9001
				$this->objectIDs = ArrayUtil::toIntegerArray($_REQUEST['id']);
			}
			else {
				// ?id=1337 or ?id=1337,9001
				$this->objectIDs = ArrayUtil::toIntegerArray(explode(',', $_REQUEST['id']));
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		parent::show();
		
		// set correct content-type
		@header('Content-Type: application/rss+xml; charset=UTF-8');
		
		// show template
		WCF::getTPL()->display($this->templateName, $this->application, false);
	}
}
