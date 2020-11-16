<?php
namespace wcf\system\user\multifactor;
use wcf\system\flood\FloodControl;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\ButtonFormField;
use wcf\system\form\builder\field\TextFormField;
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
	
	private const USER_ATTEMPTS_PER_HOUR = 5;
	
	public function __construct() {
		$this->algorithm = new Bcrypt();
	}
	
	/**
	 * Returns the number of remaining codes.
	 */
	public function getStatusText(Setup $setup): string {
		$sql = "SELECT	COUNT(*) - COUNT(useTime) AS count, MAX(useTime) AS lastUsed
			FROM	wcf".WCF_N."_user_multifactor_backup
			WHERE	setupID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$setup->getId()]);
		
		return WCF::getLanguage()->getDynamicVariable(
			'wcf.user.security.multifactor.backup.status',
			$statement->fetchArray()
		);

	}
	
	/**
	 * @inheritDoc
	 */
	public function createManagementForm(IFormDocument $form, ?Setup $setup, $returnData = null): void {
		$form->addDefaultButton(false);
		$form->successMessage('wcf.user.security.multifactor.backup.success');
		
		if ($setup) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_user_multifactor_backup
				WHERE	setupID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$setup->getId()]);
			
			$codes = $statement->fetchAll(\PDO::FETCH_ASSOC);
			
			$codes = array_map(function ($code) use ($returnData) {
				if (isset($returnData[$code['identifier']])) {
					$code['chunks'] = \str_split($returnData[$code['identifier']], self::CHUNK_LENGTH);
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
	public function processManagementForm(IFormDocument $form, Setup $setup): array {
		$formData = $form->getData();
		\assert($formData['action'] === 'generateCodes' || $formData['action'] === 'regenerateCodes');
		
		$sql = "DELETE FROM	wcf".WCF_N."_user_multifactor_backup
			WHERE		setupID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$setup->getId()]);
		
		$codes = [];
		for ($i = 0; $i < 10; $i++) {
			$chunks = [];
			for ($part = 0; $part < self::CHUNKS; $part++) {
				$chunks[] = \random_int(
					10 ** (self::CHUNK_LENGTH - 1),
					(10 ** self::CHUNK_LENGTH) - 1
				);
			}
			
			$identifier = $chunks[0];
			if (isset($codes[$identifier])) {
				continue;
			}
			
			$codes[$identifier] = \implode('', $chunks);
		}
		
		$sql = "INSERT INTO	wcf".WCF_N."_user_multifactor_backup
					(setupID, identifier, code, createTime)
			VALUES		(?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$algorithName = PasswordAlgorithmManager::getInstance()->getNameFromAlgorithm($this->algorithm);
		foreach ($codes as $identifier => $code) {
			$statement->execute([
				$setup->getId(),
				$identifier,
				$algorithName.':'.$this->algorithm->hash($code),
				\TIME_NOW,
			]);
		}
		
		return $codes;
	}
	
	/**
	 * Returns a code from $codes matching the $userCode. `null` is returned if
	 * no matching code could be found.
	 */
	private function findValidCode(string $userCode, array $codes): ?array {
		$manager = PasswordAlgorithmManager::getInstance();
		
		$result = null;
		foreach ($codes as $code) {
			[$algorithmName, $hash] = \explode(':', $code['code']);
			$algorithm = $manager->getAlgorithmFromName($algorithmName);
			
			// The use of `&` is intentional to disable the shortcutting logic.
			if ($algorithm->verify($userCode, $hash) & $code['useTime'] === null) {
				$result = $code;
			}
		}
		
		return $result;
	}
	
	/**
	 * @inheritDoc
	 */
	public function createAuthenticationForm(IFormDocument $form, Setup $setup): void {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_user_multifactor_backup
			WHERE	setupID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$setup->getId()]);
		$codes = $statement->fetchAll(\PDO::FETCH_ASSOC);
		
		$form->appendChildren([
			TextFormField::create('code')
				->label('wcf.user.security.multifactor.backup.code')
				->autoFocus()
				->required()
				->addValidator(new FormFieldValidator('code', function (TextFormField $field) use ($codes, $setup) {
					FloodControl::getInstance()->registerUserContent('com.woltlab.wcf.multifactor.backup', $setup->getId());
					$attempts = FloodControl::getInstance()->countUserContent('com.woltlab.wcf.multifactor.backup', $setup->getId(), new \DateInterval('PT1H'));
					if ($attempts['count'] > self::USER_ATTEMPTS_PER_HOUR) {
						$field->value('');
						$field->addValidationError(new FormFieldValidationError(
							'flood',
							'wcf.user.security.multifactor.backup.error.flood',
							$attempts
						));
						return;
					}
					
					$userCode = \preg_replace('/\s+/', '', $field->getValue());
					
					if ($this->findValidCode($userCode, $codes) === null) {
						$field->value('');
						$field->addValidationError(new FormFieldValidationError('invalid'));
					}
				})),
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function processAuthenticationForm(IFormDocument $form, Setup $setup): void {
		$userCode = \preg_replace('/\s+/', '', $form->getData()['data']['code']);
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_user_multifactor_backup
			WHERE	setupID = ?
			FOR UPDATE";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$setup->getId()]);
		$codes = $statement->fetchAll(\PDO::FETCH_ASSOC);
		
		$usedCode = $this->findValidCode($userCode, $codes);
		
		if ($usedCode === null) {
			throw new \RuntimeException('Unable to find a valid code.');
		}
		
		$sql = "UPDATE	wcf".WCF_N."_user_multifactor_backup
			SET	useTime = ?
			WHERE		setupID = ?
				AND	identifier = ?
				AND	useTime IS NULL";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			\TIME_NOW,
			$setup->getId(),
			$usedCode['identifier'],
		]);
		
		if ($statement->getAffectedRows() !== 1) {
			throw new \RuntimeException('Unable to invalidate the code.');
		}
	}
}
