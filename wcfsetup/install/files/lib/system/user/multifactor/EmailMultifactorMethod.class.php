<?php
namespace wcf\system\user\multifactor;
use wcf\system\background\BackgroundQueueHandler;
use wcf\system\email\SimpleEmail;
use wcf\system\flood\FloodControl;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\ButtonFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\LanguageItemFormNode;
use wcf\system\user\multifactor\email\CodeFormField;
use wcf\system\WCF;

/**
 * Implementation of one time codes sent via email.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\System\User\Multifactor
 * @since	5.4
 */
class EmailMultifactorMethod implements IMultifactorMethod {
	private const LIFETIME = 10 * 60;
	private const REFRESH_AFTER = 2 * 60;
	
	public const LENGTH = 8;
	
	private const USER_ATTEMPTS_PER_TEN_MINUTES = 5;
	
	/**
	 * Returns an empty string.
	 */
	public function getStatusText(Setup $setup): string {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function createManagementForm(IFormDocument $form, ?Setup $setup, $returnData = null): void {
		$form->addDefaultButton(false);
		$form->successMessage('wcf.user.security.multifactor.email.success');
		
		if ($setup) {
			$statusContainer = FormContainer::create('enabledContainer')
				->label('wcf.user.security.multifactor.email.enabled')
				->appendChildren([
					LanguageItemFormNode::create('enabled')
						->languageItem('wcf.user.security.multifactor.email.enabled.description'),
				]);
			$form->appendChild($statusContainer);
		}
		else {
			$generateContainer = FormContainer::create('enableContainer')
				->label('wcf.user.security.multifactor.email.enable')
				->appendChildren([
					LanguageItemFormNode::create('explanation')
						->languageItem('wcf.user.security.multifactor.email.enable.description'),
					ButtonFormField::create('enable')
						->buttonLabel('wcf.user.security.multifactor.email.enable')
						->objectProperty('action')
						->value('enable')
						->addValidator(new FormFieldValidator('enable', function (ButtonFormField $field) {
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
	public function processManagementForm(IFormDocument $form, Setup $setup): void {
		$formData = $form->getData();
		\assert($formData['action'] === 'enable');
	}
	
	/**
	 * Returns a code from $codes matching the $userCode. `null` is returned if
	 * no matching code could be found.
	 */
	private function findValidCode(string $userCode, array $codes): ?array {
		$result = null;
		foreach ($codes as $code) {
			if (\hash_equals($code['code'], $userCode)) {
				$result = $code;
			}
		}
		
		return $result;
	}
	
	/**
	 * Sends the email containing the one time code.
	 */
	private function sendEmail(Setup $setup, string $code): void {
		$email = new SimpleEmail();
		$email->setRecipient($setup->getUser());
		
		$email->setSubject(
			WCF::getLanguage()->getDynamicVariable('wcf.user.security.multifactor.email.subject', [
				'code' => $code,
			])
		);
		$email->setHtmlMessage(
			WCF::getLanguage()->getDynamicVariable('wcf.user.security.multifactor.email.body.html', [
				'code' => $code,
			])
		);
		$email->setMessage(
			WCF::getLanguage()->getDynamicVariable('wcf.user.security.multifactor.email.body.plain', [
				'code' => $code,
			])
		);
		
		$jobs = $email->getEmail()->getJobs();
		foreach ($jobs as $job) {
			BackgroundQueueHandler::getInstance()->performJob($job);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function createAuthenticationForm(IFormDocument $form, Setup $setup): void {
		$form->markRequiredFields(false);
		
		$sql = "SELECT	code, createTime
			FROM	wcf".WCF_N."_user_multifactor_email
			WHERE		setupID = ?
				AND	createTime > ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$setup->getId(),
			(\TIME_NOW - self::LIFETIME),
		]);
		$codes = $statement->fetchAll(\PDO::FETCH_ASSOC);
		
		$lastCode = 0;
		foreach ($codes as $code) {
			$lastCode = \max($lastCode, $code['createTime']);
		}
		
		if ($lastCode < (\TIME_NOW - self::REFRESH_AFTER)) {
			\assert(self::LENGTH <= 9, "Code does not fit into a 32-bit integer.");
			
			$code = \random_int(
				10 ** (self::LENGTH - 1),
				(10 ** self::LENGTH) - 1
			);
			$sql = "INSERT INTO	wcf".WCF_N."_user_multifactor_email
						(setupID, code, createTime)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				$setup->getId(),
				$code,
				\TIME_NOW,
			]);
			
			$this->sendEmail($setup, $code);
			$lastCode = \TIME_NOW;
		}
		
		$address = $setup->getUser()->email;
		$atSign = strrpos($address, '@');
		$emailDomain = substr($address, $atSign + 1);
		
		$form->appendChildren([
			CodeFormField::create()
				->label('wcf.user.security.multifactor.email.code')
				->description('wcf.user.security.multifactor.email.code.description', [
					'emailDomain' => $emailDomain,
					'lastCode' => $lastCode,
				])
				->autoFocus()
				->required()
				->addValidator(new FormFieldValidator('code', function (TextFormField $field) use ($codes, $setup) {
					FloodControl::getInstance()->registerUserContent('com.woltlab.wcf.multifactor.email', $setup->getId());
					$attempts = FloodControl::getInstance()->countUserContent('com.woltlab.wcf.multifactor.email', $setup->getId(), new \DateInterval('PT10M'));
					if ($attempts['count'] > self::USER_ATTEMPTS_PER_TEN_MINUTES) {
						$field->value('');
						$field->addValidationError(new FormFieldValidationError(
							'flood',
							'wcf.user.security.multifactor.email.error.flood',
							$attempts
						));
						return;
					}
					
					$userCode = $field->getValue();
					
					if ($this->findValidCode($userCode, $codes) === null) {
						$field->value('');
						$field->addValidationError(new FormFieldValidationError(
							'invalidCode',
							'wcf.user.security.multifactor.error.invalidCode'
						));
					}
				})),
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function processAuthenticationForm(IFormDocument $form, Setup $setup): void {
		$userCode = $form->getData()['data']['code'];
		
		$sql = "SELECT	code
			FROM	wcf".WCF_N."_user_multifactor_email
			WHERE		setupID = ?
				AND	createTime > ?
			FOR UPDATE";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$setup->getId(),
			(\TIME_NOW - self::LIFETIME),
		]);
		$codes = $statement->fetchAll(\PDO::FETCH_ASSOC);
		
		$usedCode = $this->findValidCode($userCode, $codes);
		
		if ($usedCode === null) {
			throw new \RuntimeException('Unable to find a valid code.');
		}
		
		$sql = "DELETE FROM	wcf".WCF_N."_user_multifactor_email
			WHERE		setupID = ?
				AND	createTime > ?
				AND	code = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$setup->getId(),
			(\TIME_NOW - self::LIFETIME),
			$usedCode['code'],
		]);
		
		if ($statement->getAffectedRows() !== 1) {
			throw new \RuntimeException('Unable to invalidate the code.');
		}
	}
	
	/**
	 * Deletes expired codes.
	 */
	public static function prune(): void {
		$sql = "DELETE FROM	wcf".WCF_N."_user_multifactor_email
			WHERE		createTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			(\TIME_NOW - self::LIFETIME),
		]);
	}
}
