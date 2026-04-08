<?php

namespace wcf\system\email\mime;

/**
 * Represents the visible text of an email.
 * The content type usually is either text/plain or text/html.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TextMimePart extends AbstractMimePart
{
    /**
     * the content of this text part
     * @var string
     */
    protected $content;

    /**
     * the mime type to provide in the email
     * @var string
     */
    protected $mimeType;

    /**
     * Creates a new Text.
     *
     * @param string $content Content of this text part.
     * @param string $mimeType Mime type to provide in the email. You *must* not provide a charset. UTF-8 will be used automatically.
     */
    public function __construct(string $content, string $mimeType)
    {
        $this->mimeType = $mimeType;
        $this->content = $content;
    }

    #[\Override]
    public function getContentType()
    {
        return $this->mimeType . "; charset=UTF-8";
    }

    #[\Override]
    public function getContentTransferEncoding()
    {
        return 'quoted-printable';
    }

    #[\Override]
    public function getContent()
    {
        return $this->content;
    }
}
