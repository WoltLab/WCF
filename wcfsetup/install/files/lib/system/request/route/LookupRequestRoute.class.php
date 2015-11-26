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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category	Community Framework
 */
class LookupRequestRoute implements IRequestRoute {
	/**
	 * list of parsed route information
	 * @var array
	 */
	protected $routeData = [];
	
	/**
	 * @inheritDoc
	 */
	public function matches($requestURL) {
		$requestURL = FileUtil::addLeadingSlash($requestURL);
		
		if ($requestURL === '/') {
			// ignore empty urls and let them be handled by regular routes
			return false;
		}
		
		$regex = '~^
			/
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
				$this->routeData = ControllerMap::getInstance()->resolveCustomController($application, $matches['controller']);
			}
			
			if (empty($this->routeData)) {
				// try to match the entire url
				$this->routeData = ControllerMap::getInstance()->resolveCustomController($application, $requestURL);
			}
		}
		
		return (!empty($this->routeData));
	}
	
	/**
	 * @inheritDoc
	 */
	public function getRouteData() {
		return $this->getRouteData();
	}
	
	/**
	 * @inheritDoc
	 */
	public function setIsACP($isACP) {
		// lookups are not supported for ACP requests
	}
	
	/**
	 * @inheritDoc
	 * @throws      SystemException
	 */
	public function buildLink(array $components) {
		throw new SystemException('LookupRequestRoute cannot build links, please verify capabilities by calling canHandle() first.');
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