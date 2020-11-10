<?php
namespace wcf\system\user\multifactor;
use ParagonIE\ConstantTime\Hex;
use wcf\system\flood\FloodControl;
use wcf\system\form\builder\button\FormButton;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\ButtonFormField;
use wcf\system\form\builder\field\dependency\IsNotClickedFormFieldDependency;
use wcf\system\form\builder\field\HiddenFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\user\multifactor\totp\CodeFormField;
use wcf\system\user\multifactor\totp\NewDeviceContainer;
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
	private const USER_ATTEMPTS_PER_TEN_MINUTES = 5;
	
	/**
	 * Returns the number of devices the user set up.
	 */
	public function getStatusText(int $setupId): string {
		$sql = "SELECT	COUNT(*) AS count, MAX(useTime) AS lastUsed
			FROM	wcf".WCF_N."_user_multifactor_totp
			WHERE	setupID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$setupId]);
		
		return WCF::getLanguage()->getDynamicVariable(
			'wcf.user.security.multifactor.totp.status',
			$statement->fetchArray()
		);
	}
	
	/**
	 * @inheritDoc
	 */
	public function createManagementForm(IFormDocument $form, ?int $setupId, $returnData = null): void {
		$form->addDefaultButton(false);
		$newDeviceContainer = NewDeviceContainer::create()
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
				FormButton::create('submitButton')
					->label('wcf.global.button.submit')
					->accessKey('s')
					->submit(true)
					->addClass('buttonPrimary'),
			]);
		$form->appendChild($newDeviceContainer);
		
		// Note: The order of the two parts of the form is important. Pressing submit within an input
		// will implicitly press the first submit button. If this container comes first the submit
		// button will be a delete button.
		if ($setupId) {
			$sql = "SELECT	deviceID, deviceName, createTime, useTime
				FROM	wcf".WCF_N."_user_multifactor_totp
				WHERE	setupID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$setupId]);
			$devicesContainer = FormContainer::create('devices')
				->label('wcf.user.security.multifactor.totp.devices');
			while ($row = $statement->fetchArray()) {
				$devicesContainer->appendChildren([
					$button = ButtonFormField::create('delete_'.$row['deviceID'])
						->buttonLabel($row['deviceName'])
						->objectProperty('delete')
						->value($row['deviceID']),
				]);
				
				$newDeviceContainer->addDependency(
					IsNotClickedFormFieldDependency::create('delete_'.$row['deviceID'])
						->field($button)
				);
			}
			
			$form->appendChild($devicesContainer);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function processManagementForm(IFormDocument $form, int $setupId): void {
		$formData = $form->getData();
		
		\assert(
			(!empty($formData['data']) && empty($formData['delete'])) ||
			(empty($formData['data']) && !empty($formData['delete']))
		);
		
		if (!empty($formData['delete'])) {
			$sql = "DELETE FROM	wcf".WCF_N."_user_multifactor_totp
				WHERE		setupID = ?
					AND	deviceID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				$setupId,
				$formData['delete'],
			]);
			
			$sql = "SELECT	COUNT(*)
				FROM	wcf".WCF_N."_user_multifactor_totp
				WHERE	setupID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				$setupId,
			]);
			
			if (!$statement->fetchSingleColumn()) {
				throw new \LogicException('Unreachable');
			}
		}
		else {
			$defaultName = WCF::getLanguage()->getDynamicVariable('wcf.user.security.multifactor.totp.deviceName.default');
			$sql = "INSERT INTO	wcf".WCF_N."_user_multifactor_totp
						(setupID, deviceID, deviceName, secret, minCounter, createTime)
				VALUES		(?, ?, ?, ?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				$setupId,
				Hex::encode(\random_bytes(16)),
				$formData['data']['deviceName'] ?: $defaultName,
				$formData['data']['secret'],
				$formData['data']['code']['minCounter'],
				\TIME_NOW,
			]);
		}
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
				->addValidator(new FormFieldValidator('code', function (CodeFormField $field) use ($devices, $setupId) {
					FloodControl::getInstance()->registerUserContent('com.woltlab.wcf.multifactor.backup', $setupId);
					$attempts = FloodControl::getInstance()->countUserContent('com.woltlab.wcf.multifactor.backup', $setupId, new \DateInterval('PT10M'));
					if ($attempts['count'] > self::USER_ATTEMPTS_PER_TEN_MINUTES) {
						$field->addValidationError(new FormFieldValidationError(
							'flood',
							'wcf.user.security.multifactor.totp.error.flood',
							$attempts
						));
						return;
					}
					
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
			\TIME_NOW,
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
