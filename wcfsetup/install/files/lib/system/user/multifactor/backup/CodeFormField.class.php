<?php
namespace wcf\system\user\multifactor\backup;
use wcf\system\form\builder\field\TDefaultIdFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\user\multifactor\BackupMultifactorMethod;
use wcf\system\user\multifactor\Helper;

/**
 * Handles the input of a emergency code.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\System\User\Multifactor\Backup
 * @since	5.4
 */
class CodeFormField extends TextFormField {
	use TDefaultIdFormField;
	
	/**
	 * @var int
	 */
	protected $chunks;
	
	/**
	 * @var int
	 */
	protected $chunkLength;
	
	public function __construct() {
		$this->chunks(BackupMultifactorMethod::CHUNKS);
		$this->chunkLength(BackupMultifactorMethod::CHUNK_LENGTH);
		$this->minimumLength($this->getChunks() * $this->getChunkLength());
		$this->fieldAttribute('size', $this->getChunks() - 1 + $this->getChunks() * $this->getChunkLength());
		$this->addFieldClass('multifactorBackupCode');
		$this->autoComplete('off');
		$this->inputMode('numeric');
		$this->pattern('[0-9\s]*');
		
		$placeholder = '';
		$gen = Helper::digitStream();
		for ($i = 0; $i < BackupMultifactorMethod::CHUNKS; $i++) {
			for ($j = 0; $j < BackupMultifactorMethod::CHUNK_LENGTH; $j++) {
				$placeholder .= $gen->current();
				$gen->next();
			}
			$placeholder .= ' ';
		}
		$this->placeholder($placeholder);
	}
	
	/**
	 * Sets the number of chunks.
	 */
	public function chunks(int $chunks): self {
		$this->chunks = $chunks;
		return $this;
	}
	
	/**
	 * Sets the length of a single chunk.
	 */
	public function chunkLength(int $chunkLength): self {
		$this->chunkLength = $chunkLength;
		return $this;
	}
	
	/**
	 * Returns the number of chunks.
	 */
	public function getChunks() {
		return $this->chunks;
	}
	
	/**
	 * Returns the length of a single chunk.
	 */
	public function getChunkLength() {
		return $this->chunkLength;
	}
	
	/**
	 * @inheritDoc
	 */
	protected static function getDefaultId(): string {
		return 'code';
	}
}
