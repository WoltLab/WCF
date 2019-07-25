<?php
namespace wcf\system\cache\builder;
use wcf\acp\page\ArticleListPage;
use wcf\data\application\Application;
use wcf\data\page\PageCache;
use wcf\page\CmsPage;
use wcf\system\application\ApplicationHandler;
use wcf\system\request\ControllerMap;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Caches routing data.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 * @since	3.0
 */
class RoutingCacheBuilder extends AbstractCacheBuilder {
	/**
	 * list of controllers violating the url schema, but are
	 * supported for legacy reasons
	 * @var array
	 */
	protected $brokenControllers = [
		'lookup' => [],
		'reverse' => []
	];
	
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$data = [
			'ciControllers' => $this->getCaseInsensitiveControllers(),
			'landingPages' => $this->getLandingPages()
		];
		
		$data['customUrls'] = $this->getCustomUrls($data['landingPages']);
		
		return $data;
	}
	
	/**
	 * Builds the list of controllers violating the camel-case schema by having more than
	 * two consecutive upper-case letters in the name. The list is divided on an application
	 * and environment level to prevent any issues with controllers with the same name but
	 * correct spelling to be incorrectly handled.
	 * 
	 * @return	array
	 */
	protected function getCaseInsensitiveControllers() {
		$data = [
			'lookup' => [],
			'reverse' => []
		];
		
		if (!PACKAGE_ID) {
			return $data;
		}
		
		$applications = ApplicationHandler::getInstance()->getApplications();
		$applications[1] = ApplicationHandler::getInstance()->getWCF();
		foreach ($applications as $application) {
			$abbreviation = $application->getAbbreviation();
			$directory = Application::getDirectory($abbreviation);
			foreach (['lib', 'lib/acp'] as $libDirectory) {
				foreach (['action', 'form', 'page'] as $pageType) {
					$path = $directory . $libDirectory . '/' . $pageType;
					if (!is_dir($path)) {
						continue;
					}
					
					$di = new \DirectoryIterator($path);
					foreach ($di as $file) {
						if ($file->isDir() || $file->isDot()) {
							continue;
						}
						
						$filename = $file->getBasename('.class.php');
						
						// search for files with two consecutive upper-case letters but ignore interfaces such as `IPage`
						if (!preg_match('~^I[A-Z][a-z]~', $filename) && preg_match('~[A-Z]{2,}~', $filename)) {
							$parts = preg_split('~([A-Z][a-z0-9]+)~', $filename, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
							
							// drop the last part containing `Action` or `Page`
							array_pop($parts);
							
							// fix for invalid pages that would cause single character fragments
							$sanitizedParts = [];
							$tmp = '';
							$isBrokenController = false;
							foreach ($parts as $part) {
								if (strlen($part) === 1) {
									$isBrokenController = true;
									$tmp .= $part;
									continue;
								}
								
								$sanitizedParts[] = $tmp . $part;
								$tmp = '';
							}
							if ($tmp) $sanitizedParts[] = $tmp;
							$parts = $sanitizedParts;
							
							$ciController = implode('-', array_map('strtolower', $parts));
							$className = $abbreviation . '\\' . ($libDirectory === 'lib/acp' ? 'acp\\' : '') . $pageType . '\\' . $filename;
							
							if (!isset($data['lookup'][$abbreviation])) $data['lookup'][$abbreviation] = ['acp' => [], 'frontend' => []];
							$data['lookup'][$abbreviation][$libDirectory === 'lib' ? 'frontend' : 'acp'][$ciController] = $className;
							$data['reverse'][$filename] = $ciController;
							
							if ($isBrokenController) {
								if (!isset($this->brokenControllers['lookup'][$abbreviation])) $this->brokenControllers['lookup'][$abbreviation] = [];
								$this->brokenControllers['lookup'][$abbreviation][$ciController] = $className;
								
								if (!isset($this->brokenControllers['reverse'][$abbreviation])) $this->brokenControllers['reverse'][$abbreviation] = [];
								$this->brokenControllers['reverse'][$abbreviation][preg_replace('~(?:Page|Form|Action)$~', '', $filename)] = $ciController;
							}
						}
					}
				}
			}
		}
		
		return $data;
	}
	
	/**
	 * Builds up a lookup and a reverse lookup list per application in order to resolve
	 * custom page mappings.
	 * 
	 * @param       array   $landingPages
	 * @return	array
	 */
	protected function getCustomUrls(array $landingPages) {
		$data = [
			'lookup' => [],
			'reverse' => []
		];
		
		if (!PACKAGE_ID) {
			return $data;
		}
		
		// fetch pages with a controller and a custom url
		$sql = "SELECT	controller, controllerCustomURL, applicationPackageID
			FROM	wcf".WCF_N."_page
			WHERE	controller <> ''
				AND controllerCustomURL <> ''";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
		
		// fetch content pages using the common page controller
		$sql = "SELECT		page_content.customURL AS controllerCustomURL, page_content.pageID, page_content.languageID, page.applicationPackageID
			FROM		wcf".WCF_N."_page_content page_content
			LEFT JOIN	wcf".WCF_N."_page page
			ON		(page.pageID = page_content.pageID)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$rows[] = $row;
		}
		
		$cmsPageID = 0;
		if (preg_match('~^__WCF_CMS__(\d+)$~', $landingPages['wcf'][0], $matches)) {
			$cmsPageID = $matches[1];
		}
		
		$abbreviations = [];
		foreach ($rows as $row) {
			$customUrl = FileUtil::removeLeadingSlash(FileUtil::removeTrailingSlash($row['controllerCustomURL']));
			$packageID = $row['applicationPackageID'];
			if (!isset($abbreviations[$packageID])) {
				$abbreviations[$packageID] = ApplicationHandler::getInstance()->getAbbreviation($packageID);
			}
			
			if (!isset($data['lookup'][$abbreviations[$packageID]])) {
				$data['lookup'][$abbreviations[$packageID]] = [];
				$data['reverse'][$abbreviations[$packageID]] = [];
			}
			
			if (isset($row['controller'])) {
				$data['lookup'][$abbreviations[$packageID]][$customUrl] = $row['controller'];
				$data['reverse'][$abbreviations[$packageID]][preg_replace('~^.*?([A-Za-z0-9]+)(?:Action|Form|Page)~', '$1', $row['controller'])] = $customUrl;
			}
			else {
				$cmsIdentifier = '__WCF_CMS__' . $row['pageID'] . '-' . ($row['languageID'] ?: 0);
				
				// Discard the custom url if this CMS page is the landing page of its associated app.
				if (!empty($landingPages[$abbreviations[$packageID]]) && $landingPages[$abbreviations[$packageID]][0] === '__WCF_CMS__' . $row['pageID']) {
					$customUrl = '';
				}
				$data['reverse'][$abbreviations[$packageID]][$cmsIdentifier] = $customUrl;
				
				if ($cmsPageID && $abbreviations[$packageID] === 'wcf') {
					if ($customUrl === '' && $cmsPageID != $row['pageID']) {
						continue;
					}
				}
				
				$data['lookup'][$abbreviations[$packageID]][$customUrl] = $cmsIdentifier;
				
			}
		}
		
		// masquerade broken controllers as custom urls
		foreach ($this->brokenControllers as $type => $brokenControllers) {
			foreach ($brokenControllers as $application => $controllers) {
				foreach ($controllers as $key => $value) {
					if (!isset($data[$type][$application])) {
						$data[$type][$application] = [];
					}
					
					if (!isset($data[$type][$application][$key])) {
						$data[$type][$application][$key] = $value;
					}
				}
			}
		}
		
		return $data;
	}
	
	/**
	 * Returns the list of landing pages per application.
	 * 
	 * @return	string[]
	 */
	protected function getLandingPages() {
		$data = [];
		
		if (!PACKAGE_ID) {
			return $data;
		}
		
		foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
			$controller = null;
			
			if ($application->packageID == 1) {
				// handle WCF
				$page = PageCacheBuilder::getInstance()->getData([], 'landingPage');
				if ($page === null) {
					// invalid cache data, e.g. during upgrade 2.1 -> 3.0
					$controller = ArticleListPage::class;
				}
				else if ($page->controller) {
					$controller = $page->controller;
				}
				else {
					$controller = '__WCF_CMS__' . $page->pageID;
					$controller = [$controller, $controller, CmsPage::class];
				}
			}
			else {
				if ($application->landingPageID) {
					$page = PageCache::getInstance()->getPage($application->landingPageID);
					if ($page !== null) {
						if ($page->controller) {
							$controller = $page->controller;
						}
						else {
							$controller = '__WCF_CMS__' . $page->pageID;
							$controller = [$controller, $controller, CmsPage::class];
						}
					}
				}
				
				if ($controller === null) {
					$controller = WCF::getApplicationObject($application)->getPrimaryController();
				}
			}
			
			if (is_string($controller)) {
				$fqnController = $controller;
				$controller = preg_replace('~^.*?\\\([^\\\]+)(?:Action|Form|Page)$~', '\\1', $controller);
				$controller = [
					$controller,
					ControllerMap::transformController($controller),
					$fqnController
				];
			}
			
			$data[ApplicationHandler::getInstance()->getAbbreviation($application->packageID)] = $controller;
		}
		
		return $data;
	}
}
