<?php

namespace wcf\command\tag;

use wcf\data\tag\Tag;
use wcf\data\tag\TagEditor;

/**
 * Command to set synonyms for a tag.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class SetTagSynonym
{
    /**
     * @param Tag[] $tags
     */
    public function __construct(
        private readonly Tag $mainTag,
        private readonly array $tags
    ) {}

    public function __invoke(): void
    {
        $tagEditor = new TagEditor($this->mainTag);

        // the "main" tag may not be a synonym itself
        if ($tagEditor->synonymFor) {
            $tagEditor->update([
                'synonymFor' => null,
            ]);
        }

        foreach ($this->tags as $tag) {
            $tagEditor->addSynonym($tag);
        }
    }
}
