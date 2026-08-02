<?php

namespace wcf\system\email\mime;

/**
 * Represents a multipart/alternative mime container.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class MultipartAlternativeMimePart extends AbstractMultipartMimePart
{
    #[\Override]
    public function getContentType()
    {
        return "multipart/alternative;\r\n   boundary=\"" . $this->boundary . "\"";
    }

    #[\Override]
    protected function getConcatenatedParts(\Traversable $parts)
    {
        \assert($parts instanceof \SplObjectStorage);

        /** @var \SplPriorityQueue<int, AbstractMimePart> */
        $sortedParts = new \SplPriorityQueue();

        $parts->rewind();
        while ($parts->valid()) {
            $part = $parts->current();
            \assert($part instanceof AbstractMimePart);

            $sortedParts->insert($part, \PHP_INT_MAX - $parts->getInfo());
            $parts->next();
        }

        return parent::getConcatenatedParts($sortedParts);
    }

    /**
     * Adds a mime part to this multipart container.
     *
     * The given priority determines the ordering within the Email. A higher priority
     * mime part will be further down the email (see RFC 2046, 5.1.4).
     *
     * @param mixed $data The priority, must be an integer.
     * @throws  \InvalidArgumentException
     * @throws  \DomainException
     */
    // @codingStandardsIgnoreStart
    #[\Override]
    public function addMimePart(AbstractMimePart $part, mixed $data = 1000)
    {
        if (!\is_int($data)) {
            throw new \InvalidArgumentException("The priority must be an integer.");
        }

        parent::addMimePart($part, $data);
    }

    // @codingStandardsIgnoreEnd
}
