<?php
namespace wcf\data\sitemap;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\SystemException;
use wcf\system\sitemap\SitemapHandler;
use wcf\system\WCF;

/**
 * Executes sitemap-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.sitemap
 * @category 	Community Framework
 */
class SitemapAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('getSitemap');
	
	/**
	 * Does nothing.
	 */
	public function validateGetSitemap() {
		if (isset($this->parameters['sitemapName'])) {
			SitemapHandler::getInstance()->validateSitemapName($this->parameters['sitemapName']);
		}
	}
	
	/**
	 * Returns sitemap for active application group.
	 * 
	 * @return	array
	 */
	public function getSitemap() {
		if (isset($this->parameters['sitemapName'])) {
			return array(
				'template' => SitemapHandler::getInstance()->getSitemap($this->parameters['sitemapName'])
			);
		}
		else {
			WCF::getTPL()->assign(array(
				'tree' => SitemapHandler::getInstance()->getTree(),
				'sitemap' => SitemapHandler::getInstance()->getSitemap()
			));
			
			return array(
				'template' => WCF::getTPL()->fetch('sitemap')
			);
		}
	}
}
