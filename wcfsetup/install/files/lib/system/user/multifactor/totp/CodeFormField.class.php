<?php
namespace wcf\system\user\multifactor\totp;
use wcf\system\form\builder\field\TDefaultIdFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\user\multifactor\Helper;

/**
 * Handles the input of a TOTP code.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\System\User\Multifactor\Totp
 * @since	5.4
 */
class CodeFormField extends TextFormField {
	use TDefaultIdFormField;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__multifactorTotpCodeField';
	
	/**
	 * @var ?int
	 */
	protected $minCounter;
	
	public function __construct() {
		$this->minimumLength(Totp::CODE_LENGTH);
		$this->maximumLength(Totp::CODE_LENGTH);
		$this->addFieldClass('multifactorTotpCode');
		
		$placeholder = '';
		$gen = Helper::digitStream();
		for ($i = 0; $i < $this->getMinimumLength(); $i++) {
			$placeholder .= $gen->current();
			$gen->next();
		}
		$this->placeholder($placeholder);
	}
	
	/**
	 * Used to carry the minCounter value along.
	 */
	public function minCounter(int $minCounter): self {
		$this->minCounter = $minCounter;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSaveValue(): array {
		if ($this->minCounter === null) {
			throw new \BadMethodCallException('No minCounter was set. Did you validate this field?');
		}
		
		return [
			'value' => $this->getValue(),
			'minCounter' => $this->minCounter,
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected static function getDefaultId(): string {
		return 'code';
	}
}
