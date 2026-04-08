<?php

namespace wcf\system\email\mime;

/**
 * Represents a multipart/mixed mime container.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class MultipartMixedMimePart extends AbstractMultipartMimePart
{
    #[\Override]
    public function getContentType()
    {
        return "multipart/mixed;\r\n   boundary=\"" . $this->boundary . "\"";
    }

    /**
     * Adds a mime part to this multipart container.
     *
     * The given $data is ignored.
     *
     * @param mixed $data Ignored.
     * @throws \InvalidArgumentException
     * @throws \DomainException
     */
    // @codingStandardsIgnoreStart
    #[\Override]
    public function addMimePart(AbstractMimePart $part, mixed $data = null)
    {
        parent::addMimePart($part, $data);
    }

    // @codingStandardsIgnoreEnd
}
