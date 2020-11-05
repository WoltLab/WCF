<?php
namespace wcf\system\user\multifactor;
use ParagonIE\ConstantTime\Hex;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\user\multifactor\totp\CodeFormField;
use wcf\system\user\multifactor\totp\SecretFormField;
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
}
