<?php
namespace wcf\system\request;
use wcf\system\exception\SystemException;
use wcf\system\exception\IllegalLinkException;
use wcf\system\SingletonFactory;

/**
 * Handles http requests.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category 	Community Framework
 */
class RequestHandler extends SingletonFactory {
	/**
	 * active request object
	 * @var Request
	 */
	protected $activeRequest = null;
	
	/**
	 * Handles a http request
	 *
	 * @param	string		$application
	 */
	public function handle($application = 'wcf', $isACP = false) {
		// default values
		$pageName = 'Index';
		$pageType = 'page';
		
		if (!empty($_GET['page']) || !empty($_POST['page'])) {
			$pageName = (!empty($_GET['page']) ? $_GET['page'] : $_POST['page']);
			$pageType = 'page';
		}
		else if (!empty($_GET['form']) || !empty($_POST['form'])) {
			$pageName = (!empty($_GET['form']) ? $_GET['form'] : $_POST['form']);
			$pageType = 'form';
		}
		else if (!empty($_GET['action']) || !empty($_POST['action'])) {
			$pageName = (!empty($_GET['action']) ? $_GET['action'] : $_POST['action']);
			$pageType = 'action';
		}
		
		// build request
		$this->buildRequest($pageName, $pageType, $application, $isACP);
		// start request
		$this->activeRequest->execute();
	}
	
	/**
	 * Builds a new request.
	 *
	 * @param 	string 		$pageName
	 * @param 	string 		$application
	 * @param 	string 		$pageType
	 * @param	boolean		$isACP
	 */
	protected function buildRequest($pageName, $pageType, $application, $isACP) {
		try {
			// validate class name
			if (!preg_match('~^[a-z0-9_]+$~i', $pageName)) {
				throw new SystemException("Illegal class name '".$pageName."'", 11009);
			}
			
			// find class
			$className = $application.'\\'.($isACP ? 'acp\\' : '').$pageType.'\\'.ucfirst($pageName).ucfirst($pageType);
			if ($application != 'wcf' && !class_exists($className)) {
				$className = 'wcf\\'.($isACP ? 'acp\\' : '').$pageType.'\\'.ucfirst($pageName).ucfirst($pageType);
			}
			if (!class_exists($className)) {
				throw new SystemException("unable to find class '".$className."'", 11000);
			}
			
			$this->activeRequest = new Request($className, $pageName, $pageType);
		}
		catch (SystemException $e) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * Returns the active request object.
	 *
	 * @return Request
	 */
	public function getActiveRequest() {
		return $this->activeRequest;
	}
}
?>