<?php
namespace wcf\action;
use wcf\system\exception\IllegalLinkException;
use wcf\util\JSON;

/**
 * Internal action used to run a test for url rewriting.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Action
 * @since       3.1
 */
class CoreRewriteTestAction extends AbstractAction {
	/**
	 * @inheritDoc
	 * 
	 * @throws      IllegalLinkException
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!isset($_GET['uuidHash']) || \hash_equals(hash('sha256', WCF_UUID), $_GET['uuidHash'])) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		header('Access-Control-Allow-Origin: *');
		header('Content-type: application/json');
		echo JSON::encode(['core_rewrite_test' => 'passed']);
		exit;
	}
}
