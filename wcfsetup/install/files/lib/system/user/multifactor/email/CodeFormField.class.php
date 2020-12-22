<?php
namespace wcf\system\user\multifactor\email;
use wcf\system\form\builder\field\TDefaultIdFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\user\multifactor\EmailMultifactorMethod;
use wcf\system\user\multifactor\Helper;

/**
 * Handles the input of an email code.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\System\User\Multifactor\Email
 * @since	5.4
 */
class CodeFormField extends TextFormField {
	use TDefaultIdFormField;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__multifactorEmailCodeField';
	
	public function __construct() {
		$this->minimumLength(EmailMultifactorMethod::LENGTH);
		$this->maximumLength(EmailMultifactorMethod::LENGTH);
		$this->addFieldClass('multifactorEmailCode');
		$this->autoComplete('off');
		$this->inputMode('numeric');
		
		$placeholder = '';
		$gen = Helper::digitStream();
		for ($i = 0; $i < $this->getMinimumLength(); $i++) {
			$placeholder .= $gen->current();
			$gen->next();
		}
		$this->placeholder($placeholder);
	}
	
	/**
	 * @inheritDoc
	 */
	protected static function getDefaultId(): string {
		return 'code';
	}
}
