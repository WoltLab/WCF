<?php
namespace wcf\system\user\multifactor;
use ParagonIE\ConstantTime\Hex;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\HiddenFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\user\multifactor\totp\CodeFormField;
use wcf\system\user\multifactor\totp\SecretFormField;
use wcf\system\user\multifactor\totp\Totp;
use wcf\system\WCF;

/**
 * Implementation of the Time-based One-time Password Algorithm (RFC 6238).
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\System\User\Multifactor
 * @since	5.4
 */
class TotpMultifactorMethod implements IMultifactorMethod {
	/**
	 * Returns the number of devices the user set up.
	 */
	public function getStatusText(int $setupId): string {
		$sql = "SELECT	COUNT(*)
			FROM	wcf".WCF_N."_user_multifactor_totp
			WHERE	setupID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$setupId]);
		
		// TODO: Language item
		return $statement->fetchSingleColumn()." devices configured";
	}
	
	/**
	 * @inheritDoc
	 */
	public function createManagementForm(IFormDocument $form, ?int $setupId, $returnData = null): void {
		if ($setupId) {
			
		}
		
		$newDeviceContainer = FormContainer::create('newDevice')
			->label('wcf.user.security.multifactor.totp.newDevice')
			->appendChildren([
				SecretFormField::create(),
				CodeFormField::create()
					->label('wcf.user.security.multifactor.totp.code')
					->required()
					->addValidator(new FormFieldValidator('totpSecretValid', function (CodeFormField $field) {
						/** @var SecretFormField $secret */
						$secret = $field->getDocument()->getNodeById('secret');
						$totp = $secret->getTotp();
						
						$minCounter = 0;
						if (!$totp->validateTotpCode($field->getValue(), $minCounter, new \DateTime())) {
							$field->addValidationError(new FormFieldValidationError('invalid'));
						}
						$field->minCounter($minCounter);
					})),
				TextFormField::create('deviceName')
					->label('wcf.user.security.multifactor.totp.deviceName')
					->placeholder('wcf.user.security.multifactor.totp.deviceName.placeholder'),
			]);
		$form->appendChild($newDeviceContainer);
	}
	
	/**
	 * @inheritDoc
	 */
	public function processManagementForm(IFormDocument $form, int $setupId): void {
		$formData = $form->getData();

		$sql = "INSERT INTO wcf".WCF_N."_user_multifactor_totp (setupID, deviceID, deviceName, secret, minCounter, createTime) VALUES (?, ?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$setupId,
			Hex::encode(\random_bytes(16)),
			$formData['data']['deviceName'],
			$formData['data']['secret'],
			$formData['data']['code']['minCounter'],
			TIME_NOW,
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function createAuthenticationForm(IFormDocument $form, int $setupId): void {
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_user_multifactor_totp
			WHERE		setupID = ?
			ORDER BY	deviceName";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$setupId]);
		$devices = $statement->fetchAll(\PDO::FETCH_ASSOC);
		
		if (count($devices) > 1) {
			$deviceOptions = [];
			$mostRecentlyUsed = null;
			foreach ($devices as $device) {
				$deviceOptions[$device['deviceID']] = $device['deviceName'];
				
				if ($mostRecentlyUsed === null || $mostRecentlyUsed['useTime'] < $device['useTime']) {
					$mostRecentlyUsed = $device;
				}
			}
			
			$form->appendChildren([
				RadioButtonFormField::create('device')
					->label('wcf.user.security.multifactor.totp.deviceName')
					->objectProperty('deviceID')
					->options($deviceOptions)
					->value($mostRecentlyUsed['deviceID']),
			]);
		}
		else {
			$form->appendChildren([
				HiddenFormField::create('device')
					->objectProperty('deviceID')
					->value($devices[0]['deviceID']),
			]);
		}
		
		$form->appendChildren([
			CodeFormField::create()
				->label('wcf.user.security.multifactor.totp.code')
				->autoFocus()
				->required()
				->addValidator(new FormFieldValidator('code', function (CodeFormField $field) use ($devices) {
					/** @var IFormField $deviceField */
					$deviceField = $field->getDocument()->getNodeById('device');
					
					$selectedDevice = null;
					foreach ($devices as $device) {
						if ($device['deviceID'] === $deviceField->getValue()) {
							$selectedDevice = $device;
						}
					}
					if ($selectedDevice === null) {
						$field->addValidationError(new FormFieldValidationError('invalid'));
					}
					
					$totp = new Totp($selectedDevice['secret']);
					$minCounter = $selectedDevice['minCounter'];
					if (!$totp->validateTotpCode($field->getValue(), $minCounter, new \DateTime())) {
						$field->addValidationError(new FormFieldValidationError('invalid'));
					}
					$field->minCounter($minCounter);
				})),
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function processAuthenticationForm(IFormDocument $form, int $setupId): void {
		$formData = $form->getData();
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_user_multifactor_totp
			WHERE		setupID = ?
				AND	deviceID = ?
			FOR UPDATE";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$setupId,
			$formData['data']['deviceID'],
		]);
		$device = $statement->fetchArray();
		
		if ($device === null) {
			throw new \RuntimeException('Unable to find the device.');
		}
		
		$sql = "UPDATE	wcf".WCF_N."_user_multifactor_totp
			SET	useTime = ?,
				minCounter = ?
			WHERE		setupID = ?
				AND	deviceID = ?
				AND	minCounter < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			TIME_NOW,
			$formData['data']['code']['minCounter'],
			$setupId,
			$formData['data']['deviceID'],
			$formData['data']['code']['minCounter'],
		]);
		
		if ($statement->getAffectedRows() !== 1) {
			throw new \RuntimeException('Unable to invalidate the code.');
		}
	}
}
