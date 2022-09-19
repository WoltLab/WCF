<?php

namespace wcf\system;

use wcf\util\StringUtil;

/**
 * Handles meta tags.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Message
 */
final class MetaTagHandler extends SingletonFactory implements \Countable, \Iterator
{
    /**
     * current iterator index
     */
    protected int $index = 0;

    /**
     * list of index to object relation
     * @var int[]
     */
    protected $indexToObject = [];

    /**
     * list of meta tags
     * @var array
     */
    protected $objects = [];

    /**
     * @inheritDoc
     */
    protected function init(): void
    {
        // set default tags
        if ($value = WCF::getLanguage()->get(META_DESCRIPTION)) {
            $this->addTag('description', 'description', $value);
        }
        if ($value = WCF::getLanguage()->get(PAGE_TITLE)) {
            $this->addTag('og:site_name', 'og:site_name', $value, true);
        }
        if (OG_IMAGE) {
            $this->addTag(
                'og:image',
                'og:image',
                (\preg_match('~^https?://~', OG_IMAGE) ? OG_IMAGE : WCF::getPath() . OG_IMAGE),
                true
            );
        }
        if (FB_SHARE_APP_ID) {
            $this->addTag('fb:app_id', 'fb:app_id', FB_SHARE_APP_ID, true);
        }
    }

    /**
     * Adds or replaces a meta tag.
     */
    public function addTag(string $identifier, string $name, string $value, bool $isProperty = false): void
    {
        if (!isset($this->objects[$identifier])) {
            $this->indexToObject[] = $identifier;
        }

        $this->objects[$identifier] = [
            'isProperty' => $isProperty,
            'name' => $name,
            'value' => $value,
        ];

        // replace description if Open Graph Protocol tag was given
        if ($name == 'og:description' && $value) {
            $this->addTag('description', 'description', $value);
        }
    }

    /**
     * Removes a meta tag.
     */
    public function removeTag(string $identifier): void
    {
        if (isset($this->objects[$identifier])) {
            unset($this->objects[$identifier]);

            $this->indexToObject = \array_keys($this->objects);
        }
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return \count($this->objects);
    }

    /**
     * @inheritDoc
     */
    public function current(): string
    {
        $tag = $this->objects[$this->indexToObject[$this->index]];

        return '<meta ' . ($tag['isProperty'] ? 'property' : 'name') . '="' . $tag['name'] . '" content="' . StringUtil::encodeHTML($tag['value']) . '">';
    }

    /**
     * CAUTION: This methods does not return the current iterator index,
     * rather than the object key which maps to that index.
     *
     * @see \Iterator::key()
     */
    public function key(): string
    {
        return $this->indexToObject[$this->index];
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->index++;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return isset($this->indexToObject[$this->index]);
    }
}
