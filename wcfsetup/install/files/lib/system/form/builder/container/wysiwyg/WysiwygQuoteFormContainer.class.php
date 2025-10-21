<?php

namespace wcf\system\form\builder\container\wysiwyg;

use wcf\system\style\FontAwesomeIcon;

/**
 * Represents the form container for the quote-related fields below a WYSIWYG editor.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class WysiwygQuoteFormContainer extends WysiwygTabFormContainer
{
    protected $templateName = 'shared_wysiwygQuoteFormContainer';

    public function __construct()
    {
        $this->icon(FontAwesomeIcon::fromValues('quote-left'))
            ->name('quotes')
            ->label('wcf.bbcode.quote.messageTab');
    }

    #[\Override]
    public function isAvailable()
    {
        return $this->available;
    }
}
