<?php
namespace wcf\system\user\multifactor;
use wcf\data\user\User;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\ButtonFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\TemplateFormNode;
use wcf\system\user\authentication\password\algorithm\Bcrypt;
use wcf\system\user\authentication\password\IPasswordAlgorithm;
use wcf\system\user\authentication\password\PasswordAlgorithmManager;
use wcf\system\WCF;

/**
 * Implementation of random backup codes.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\System\User\Multifactor
 * @since	5.4
 */
class BackupMultifactorMethod implements IMultifactorMethod {
	/**
	 * @var IPasswordAlgorithm
	 */
	private $algorithm;
	
	private const CHUNKS = 4;
	private const CHUNK_LENGTH = 5;
	
	public function __construct() {
		$this->algorithm = new Bcrypt();
	}
	
	/**
	 * Returns the number of remaining codes.
	 */
	public function getStatusText(User $user): string {
		// TODO: Return a proper text.
		return random_int(10000, 99999)." codes remaining";
	}
	
	/**
	 * @inheritDoc
	 */
	public function createManagementForm(IFormDocument $form, ?int $setupId, $returnData = null): void {
		$form->addDefaultButton(false);
		$form->successMessage('wcf.user.security.multifactor.backup.success');
		
		if ($setupId) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_user_multifactor_backup
				WHERE	setupID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$setupId]);
			
			$codes = $statement->fetchAll(\PDO::FETCH_ASSOC);
			
			$codes = array_map(function ($code) use ($returnData) {
				if (isset($returnData[$code['identifier']])) {
					$code['chunks'] = str_split($returnData[$code['identifier']], self::CHUNK_LENGTH);
				}
				else {
					$code['chunks'] = [
						$code['identifier'],
					];
					
					while (\count($code['chunks']) < self::CHUNKS) {
						$code['chunks'][] = \str_repeat('x', self::CHUNK_LENGTH);
					}
				}
				
				return $code;
			}, $codes);
			
			$statusContainer = FormContainer::create('existingCodesContainer')
				->label('wcf.user.security.multifactor.backup.existingCodes')
				->appendChildren([
					TemplateFormNode::create('existingCodes')
						->templateName('multifactorManageBackup')
						->variables([
							'codes' => $codes,
						]),
				]);
			$form->appendChild($statusContainer);
		
			$regenerateContainer = FormContainer::create('regenerateCodesContainer')
				->label('wcf.user.security.multifactor.backup.regenerateCodes')
				->appendChildren([
					ButtonFormField::create('regenerateCodes')
						->buttonLabel('wcf.user.security.multifactor.backup.regenerateCodes')
						->objectProperty('action')
						->value('regenerateCodes')
						->addValidator(new FormFieldValidator('regenerateCodes', function (ButtonFormField $field) {
							if ($field->getValue() === null) {
								$field->addValidationError(new FormFieldValidationError('unreachable', 'unreachable'));
							}
						})),
				]);
			$form->appendChild($regenerateContainer);
		}
		else {
			$generateContainer = FormContainer::create('generateCodesContainer')
				->label('wcf.user.security.multifactor.backup.generateCodes')
				->appendChildren([
					ButtonFormField::create('generateCodes')
						->buttonLabel('wcf.user.security.multifactor.backup.generateCodes')
						->objectProperty('action')
						->value('generateCodes')
						->addValidator(new FormFieldValidator('generateCodes', function (ButtonFormField $field) {
							if ($field->getValue() === null) {
								$field->addValidationError(new FormFieldValidationError('unreachable', 'unreachable'));
							}
						})),
				]);
			$form->appendChild($generateContainer);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function processManagementForm(IFormDocument $form, int $setupId): array {
		$formData = $form->getData();
		assert($formData['action'] === 'generateCodes' || $formData['action'] === 'regenerateCodes');
		
		$sql = "DELETE FROM	wcf".WCF_N."_user_multifactor_backup
			WHERE		setupID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$setupId]);
		
		$codes = [];
		for ($i = 0; $i < 10; $i++) {
			$chunks = [];
			for ($part = 0; $part < self::CHUNKS; $part++) {
				$chunks[] = \random_int(
					pow(10, self::CHUNK_LENGTH - 1),
					pow(10, self::CHUNK_LENGTH) - 1
				);
			}
			
			$identifier = $chunks[0];
			if (isset($codes[$identifier])) {
				continue;
			}
			
			$codes[$identifier] = implode('', $chunks);
		}
		
		$sql = "INSERT INTO	wcf".WCF_N."_user_multifactor_backup
					(setupID, identifier, code, createTime)
			VALUES		(?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$algorithName = PasswordAlgorithmManager::getInstance()->getNameFromAlgorithm($this->algorithm);
		foreach ($codes as $identifier => $code) {
			$statement->execute([
				$setupId,
				$identifier,
				$algorithName.':'.$this->algorithm->hash($code),
				TIME_NOW,
			]);
		}
		
		return $codes;
	}
}
