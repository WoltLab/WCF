<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\message\censorship\Censorship;

/**
 * Provides default implementations of `ICensorshipFormField` methods.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
trait TCensorshipFormField
{
    protected bool $censorship = false;

    #[\Override]
    public function censorship(bool $censorship = true): static
    {
        $this->censorship = $censorship;

        return $this;
    }

    #[\Override]
    public function hasCensorship(): bool
    {
        return $this->censorship;
    }

    #[\Override]
    public function validateCensorship(string $text): void
    {
        if (!$this->hasCensorship()) {
            return;
        }

        if (!($this instanceof IFormField)) {
            return;
        }

        $censoredWords = Censorship::getInstance()->test($text);
        if ($censoredWords) {
            $this->addValidationError(new FormFieldValidationError(
                'censoredWords',
                'wcf.message.error.censoredWordsFound',
                ['censoredWords' => $censoredWords]
            ));
        }
    }
}
