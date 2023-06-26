<?php

namespace wcf\system\user\multifactor\backup;

use wcf\system\form\builder\field\TDefaultIdFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\user\multifactor\BackupMultifactorMethod;
use wcf\system\user\multifactor\Helper;

/**
 * Handles the input of a backup code.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.4
 */
final class CodeFormField extends TextFormField
{
    use TDefaultIdFormField;

    protected int $chunks;

    protected int $chunkLength;

    public function __construct()
    {
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
     *
     * @return $this
     */
    public function chunks(int $chunks): static
    {
        $this->chunks = $chunks;

        return $this;
    }

    /**
     * Sets the length of a single chunk.
     *
     * @return $this
     */
    public function chunkLength(int $chunkLength): static
    {
        $this->chunkLength = $chunkLength;

        return $this;
    }

    /**
     * Returns the number of chunks.
     */
    public function getChunks(): int
    {
        return $this->chunks;
    }

    /**
     * Returns the length of a single chunk.
     */
    public function getChunkLength(): int
    {
        return $this->chunkLength;
    }

    /**
     * @inheritDoc
     */
    protected static function getDefaultId(): string
    {
        return 'code';
    }
}
