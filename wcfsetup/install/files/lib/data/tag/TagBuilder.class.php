<?php

namespace wcf\data\tag;

use wcf\data\DatabaseObjectBuilder;

/**
 * Builder for creating, updating and deleting tags.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<Tag>
 */
final class TagBuilder extends DatabaseObjectBuilder
{
    public function setTagID(int $tagID): static
    {
        $this->properties['tagID'] = $tagID;

        return $this;
    }

    public function setLanguageID(int $languageID): static
    {
        $this->properties['languageID'] = $languageID;

        return $this;
    }

    public function setName(string $name): static
    {
        $this->properties['name'] = $name;

        return $this;
    }

    public function setSynonymFor(Tag $tag): static
    {
        $this->properties['synonymFor'] = $tag->tagID;

        return $this;
    }
}
