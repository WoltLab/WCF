<?php

namespace wcf\system\rssFeed;

/**
 * Simplifies the use of SimpleXMLElement within the generation of RSS feeds.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class XmlElement extends \SimpleXMLElement
{
    public function addChild(string $name, ?string $value = null, ?string $namespace = null): ?static
    {
        if ($value !== null && \is_string($value)) {
            $value = \str_replace('&', '&amp;', $value);
        }

        return parent::addChild($name, $value, $namespace);
    }

    public function addChildCData(string $name, string $value): static
    {
        $child = $this->addChild($name);
        $child->addCData($value);

        return $child;
    }

    private function addCData(string $value): void
    {
        $node = \dom_import_simplexml($this);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($value));
    }
}
