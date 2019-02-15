<?php
namespace wcf\action;
use wcf\system\exception\AJAXException;
use wcf\system\exception\UserInputException;
use wcf\system\file\upload\UploadFile;
use wcf\system\file\upload\UploadHandler;
use wcf\util\JSON;

/**
 * Copy of the default implementation for file uploads using the AJAX-API.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Action
 * @since	5.2
 */
class AJAXFileDeleteAction extends AbstractSecureAction {
	use TAJAXException;
	
	/**
	 * The internal upload id.
	 * @var string 
	 */
	public $internalId;
	
	/**
	 * The unique file id.
	 * @var string 
	 */
	public $uniqueFileId;
	
	/**
	 * The adressed file.
	 * @var UploadFile
	 */
	private $file; 
	
	/**
	 * @var UploadFile[]
	 */
	public $uploadedFiles = [];
	
	/**
	 * @inheritDoc
	 */
	public function __run() {
		try {
			parent::__run();
		}
		catch (\Throwable $e) {
			if ($e instanceof AJAXException) {
				throw $e;
			}
			else {
				$this->throwException($e);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['internalId'])) {
			$this->internalId = $_POST['internalId'];
		}
		
		if (!UploadHandler::getInstance()->isValidInternalId($this->internalId)) {
			throw new UserInputException('internalId', 'invalid');
		}
		
		if (isset($_POST['uniqueFileId'])) {
			$this->uniqueFileId = $_POST['uniqueFileId'];
		}
		
		if (!UploadHandler::getInstance()->isValidUniqueFileId($this->internalId, $this->uniqueFileId)) {
			throw new UserInputException('uniqueFileId', 'invalid');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		UploadHandler::getInstance()->removeFile($this->internalId, $this->uniqueFileId);
		
		$this->sendJsonResponse([
			'uniqueFileId' => $this->uniqueFileId
		]);
	}
	
	/**
	 * Sends a JSON-encoded response.
	 *
	 * @param	array		$data
	 */
	protected function sendJsonResponse(array $data) {
		$json = JSON::encode($data);
		
		// send JSON response
		header('Content-type: application/json');
		echo $json;
		exit;
	}
}
