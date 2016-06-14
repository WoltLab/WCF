<?php
namespace wcf\system\request\route;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\SystemException;
use wcf\system\request\ControllerMap;
use wcf\util\FileUtil;

/**
 * Attempts to resolve arbitrary request URLs against the list of known custom
 * controller URLs, optionally recognizing id and title parameter.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Request
 * @since	3.0
 */
class LookupRequestRoute implements IRequestRoute {
	/**
	 * list of parsed route information
	 * @var	array
	 */
	protected $routeData = [];
	
	/**
	 * @inheritDoc
	 */
	public function matches($requestURL) {
		$requestURL = FileUtil::removeLeadingSlash($requestURL);
		
		if ($requestURL === '') {
			// ignore empty urls and let them be handled by regular routes
			return false;
		}
		
		$regex = '~^
			(?P<controller>.+?)
			(?:
				(?P<id>[0-9]+)
				(?:
					-
					(?P<title>[^/]+)
				)?
				/
			)?
		$~x';
		
		if (preg_match($regex, $requestURL, $matches)) {
			$application = ApplicationHandler::getInstance()->getActiveApplication()->getAbbreviation();
			if (!empty($matches['id'])) {
				// check for static controller URLs
				$this->routeData = ControllerMap::getInstance()->resolveCustomController($application, FileUtil::removeTrailingSlash($matches['controller']));
				
				// lookup WCF controllers unless initial request targeted WCF itself
				if (empty($this->routeData) && $application !== 'wcf') {
					$this->routeData = ControllerMap::getInstance()->resolveCustomController('wcf', FileUtil::removeTrailingSlash($matches['controller']));
				}
				
				if (!empty($this->routeData)) {
					if (!empty($matches['id'])) {
						$this->routeData['id'] = $matches['id'];
						
						if (!empty($matches['title'])) {
							$this->routeData['title'] = $matches['title'];
						}
					}
				}
			}
			
			if (empty($this->routeData)) {
				// try to match the entire url
				$this->routeData = ControllerMap::getInstance()->resolveCustomController($application, FileUtil::removeTrailingSlash($requestURL));
				
				// lookup WCF controllers unless initial request targeted WCF itself
				if (empty($this->routeData) && $application !== 'wcf') {
					$this->routeData = ControllerMap::getInstance()->resolveCustomController('wcf', FileUtil::removeTrailingSlash($requestURL));
				}
			}
		}
		
		if (!empty($this->routeData)) {
			$this->routeData['isDefaultController'] = false;
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getRouteData() {
		return $this->routeData;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setIsACP($isACP) {
		throw new \BadMethodCallException('lookups are not supported for ACP requests');
	}
	
	/**
	 * @inheritDoc
	 * @throws	\BadMethodCallException
	 */
	public function buildLink(array $components) {
		throw new \BadMethodCallException('LookupRequestRoute cannot build links, please verify capabilities by calling canHandle() first.');
	}
	
	/**
	 * @inheritDoc
	 */
	public function canHandle(array $components) {
		// this route cannot build routes, it is a one-way resolver
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isACP() {
		// lookups are not supported for ACP requests
		return false;
	}
}
