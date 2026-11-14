<?php

namespace wcf\system\file\processor\exception;

use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Rejects an uploaded file in `IFileProcessor::validateUpload()` with a message
 * that tells the user why the file was rejected.
 *
 * The message is plain text, the client never interprets it as HTML. The
 * client receives the code and the message as the `code` and the `message` of
 * the validation error whose `param` is `validation`.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UploadRejected extends UserInputException
{
    /**
     * @param string $code identifies the reason for custom clients
     * @param string $message plain text in the language of the active user, the client shows the
     *                        phrase `wcf.upload.validation.error.{$code}` instead if it is empty
     */
    public function __construct(string $code, string $message = '')
    {
        parent::__construct('validation', $code);

        $this->message = $message;
    }

    /**
     * Uses the phrase in the language of the active user as the message.
     *
     * @param array<string, mixed> $variables
     */
    public static function fromPhrase(string $code, string $languageItem, array $variables = []): self
    {
        $html = WCF::getLanguage()->getDynamicVariable($languageItem, $variables);

        return new self($code, StringUtil::decodeHTML(StringUtil::stripHTML($html)));
    }
}
