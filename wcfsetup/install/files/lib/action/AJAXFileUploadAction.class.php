<?php
namespace wcf\action;
use wcf\system\exception\AJAXException;
use wcf\system\exception\UserInputException;
use wcf\system\file\upload\UploadFile;
use wcf\system\file\upload\UploadHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\ImageUtil;
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
class AJAXFileUploadAction extends AbstractSecureAction {
	use TAJAXException;
	
	/**
	 * The internal upload id.
	 * @var string 
	 */
	public $internalId;
	
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
		
		if (!isset($_FILES['__files']) || !is_array($_FILES['__files']) || !isset($_FILES['__files']['tmp_name']) || !is_array($_FILES['__files']['tmp_name'])) {
			throw new UserInputException('files', 'failed');
		}
		
		if (UploadHandler::getInstance()->getFieldByInternalId($this->internalId)->getMaxFiles() !== null && UploadHandler::getInstance()->getFieldByInternalId($this->internalId)->getMaxFiles() < UploadHandler::getInstance()->getFilesCountByInternalId($this->internalId) + count($_FILES['__files']['tmp_name'])) {
			throw new UserInputException('files', 'reachedRemainingLimit');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		$response = [
			'files' => [],
			'error' => []
		];
		
		$i = 0;
		
		$field = UploadHandler::getInstance()->getFieldByInternalId($this->internalId);
		
		foreach ($_FILES['__files']['tmp_name'] as $id => $tmpName) {
			if ($field->isImageOnly() && !ImageUtil::isImage($tmpName, $_FILES['__files']['name'][$id], $field->svgImageAllowed())) {
				$response['error'][$i++] = [
					'filename' => $_FILES['__files']['name'][$id],
					'errorMessage' => WCF::getLanguage()->get('wcf.upload.error.noImage')
				];
				continue;
			}
			
			$tmpFile = FileUtil::getTemporaryFilename('fileUpload_');
			
			if (!@move_uploaded_file($tmpName, $tmpFile)) {
				$response['error'][$i++] = [
					'filename' => $_FILES['__files']['name'][$id],
					'errorMessage' => WCF::getLanguage()->get('wcf.upload.error.uploadFailed')
				];
				continue;
			}
			
			$uploadFile = new UploadFile($tmpFile, $_FILES['__files']['name'][$id], true, false, $field->svgImageAllowed());
			
			UploadHandler::getInstance()->addFileByInternalId($this->internalId, $uploadFile);
			
			$this->uploadedFiles[$i++] = $uploadFile;
		}
		
		$this->executed();
		
		foreach ($this->uploadedFiles as $id => $file) {
			$response['files'][$id] = [
				'filename' => $file->getFilename(),
				'icon' => $file->getIconName(),
				'filesize' => FileUtil::formatFilesize($file->filesize),
				'image' => $file->viewableImage ? $file->getImage() : null, 
				'uniqueFileId' => $file->getUniqueFileId()
			];
		}
		
		$this->sendJsonResponse($response);
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
