<?php
namespace wcf\system\package;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\system\form\FormDocument;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Handels form documents associated with a queue.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Form
 */
abstract class PackageInstallationFormManager {
	/**
	 * Handles a POST or GET request.
	 * 
	 * @param	PackageInstallationQueue	$queue
	 */
	public static function handleRequest(PackageInstallationQueue $queue) {
		$formName = (isset($_REQUEST['formName'])) ? StringUtil::trim($_REQUEST['formName']) : '';
		
		// ignore request
		if (empty($formName) || !self::findForm($queue, $formName)) return;
		
		// get document
		$document = self::getForm($queue, $formName);
		$document->handleRequest();
		
		self::updateForm($queue, $document);
	}
	
	/**
	 * Registers a form document.
	 * 
	 * @param	PackageInstallationQueue	$queue
	 * @param	FormDocument			$document
	 */
	public static function registerForm(PackageInstallationQueue $queue, FormDocument $document) {
		if (self::findForm($queue, $document->getName())) {
			self::updateForm($queue, $document);
		}
		else {
			self::insertForm($queue, $document);
		}
	}
	
	/**
	 * Searches for an existing form document associated with given queue.
	 * 
	 * @param	PackageInstallationQueue	$queue
	 * @param	string				$formName
	 * @return	boolean
	 */
	public static function findForm(PackageInstallationQueue $queue, $formName) {
		$sql = "SELECT	COUNT(*)
			FROM	wcf".WCF_N."_package_installation_form
			WHERE	queueID = ?
				AND formName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$queue->queueID,
			$formName
		]);
		
		return $statement->fetchSingleColumn() > 0;
	}
	
	/**
	 * Inserts a form document into database.
	 * 
	 * @param	PackageInstallationQueue	$queue
	 * @param	FormDocument			$document
	 */
	private static function insertForm(PackageInstallationQueue $queue, FormDocument $document) {
		$sql = "INSERT INTO	wcf".WCF_N."_package_installation_form
					(queueID, formName, document)
			VALUES		(?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$queue->queueID,
			$document->getName(),
			base64_encode(serialize($document))
		]);
	}
	
	/**
	 * Updates a form document database entry.
	 * 
	 * @param	PackageInstallationQueue	$queue
	 * @param	FormDocument			$document
	 */
	private static function updateForm(PackageInstallationQueue $queue, FormDocument $document) {
		$sql = "UPDATE	wcf".WCF_N."_package_installation_form
			SET	document = ?
			WHERE	queueID = ?
				AND formName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			base64_encode(serialize($document)),
			$queue->queueID,
			$document->formName // TODO: FormDocument::$formName does not exist, FormDocument::getName()?
		]);
	}
	
	/**
	 * Deletes form documents associated with given queue.
	 * 
	 * @param	PackageInstallationQueue	$queue
	 */
	public static function deleteForms(PackageInstallationQueue $queue) {
		$sql = "DELETE FROM	wcf".WCF_N."_package_installation_form
			WHERE		queueID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$queue->queueID]);
	}
	
	/**
	 * Returns a form document from database.
	 * 
	 * @param	PackageInstallationQueue	$queue
	 * @param	string				$formName
	 * @return	FormDocument
	 */
	public static function getForm(PackageInstallationQueue $queue, $formName) {
		$sql = "SELECT	document
			FROM	wcf".WCF_N."_package_installation_form
			WHERE	queueID = ?
				AND formName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$queue->queueID,
			$formName
		]);
		$row = $statement->fetchArray();
		
		if ($row) {
			$document = unserialize(base64_decode($row['document']));
			return $document;
		}
		
		return null;
	}
}
