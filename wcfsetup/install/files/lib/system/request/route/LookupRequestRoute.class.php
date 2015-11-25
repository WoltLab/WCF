<?php
namespace wcf\system\request\route;

use wcf\system\exception\SystemException;
use wcf\system\request\ControllerMap;
use wcf\util\FileUtil;

class LookupRequestRoute implements IRequestRoute {
	/**
	 * @var ControllerMap
	 */
	protected $controllerMap;
	
	protected $routeData = [];
	
	public function __construct(ControllerMap $controllerMap) {
		$this->controllerMap = $controllerMap;
	}
	
	/**
	 * @inheritDoc
	 */
	public function matches($application, $requestURL) {
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
			if (!empty($matches['id'])) {
				// check for static controller URLs
				$this->routeData = $this->controllerMap->resolveCustomController($application, $matches['controller']);
			}
			
			if (empty($this->routeData)) {
				// try to match the entire url
				$this->routeData = $this->controllerMap->resolveCustomController($application, $requestURL);
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