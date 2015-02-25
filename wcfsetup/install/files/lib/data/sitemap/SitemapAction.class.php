<?php
namespace wcf\data\sitemap;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\sitemap\SitemapHandler;
use wcf\system\WCF;

/**
 * Executes sitemap-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.sitemap
 * @category	Community Framework
 */
class SitemapAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('getSitemap');
	
	/**
	 * Validates the 'getSitemap' action.
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
				'sitemapName' => $this->parameters['sitemapName'],
				'template' => SitemapHandler::getInstance()->getSitemap($this->parameters['sitemapName'])
			);
		}
		else {
			$sitemapName = SitemapHandler::getInstance()->getDefaultSitemapName();
			
			WCF::getTPL()->assign(array(
				'defaultSitemapName' => $sitemapName,
				'sitemap' => SitemapHandler::getInstance()->getSitemap($sitemapName),
				'tree' => SitemapHandler::getInstance()->getTree()
			));
			
			return array(
				'sitemapName' => $sitemapName,
				'template' => WCF::getTPL()->fetch('sitemap')
			);
		}
	}
}
