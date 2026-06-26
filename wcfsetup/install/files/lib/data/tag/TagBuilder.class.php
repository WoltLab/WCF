<?php

namespace wcf\data\tag;

use wcf\data\DatabaseObjectBuilder;
use wcf\system\WCF;

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
    /**
     * @var ?list<string>
     */
    private ?array $synonyms = null;

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

    /**
     * @param list<string> $synonyms
     */
    public function setSynonyms(array $synonyms): static
    {
        $this->synonyms = $synonyms;

        return $this;
    }

    #[\Override]
    protected function afterCreate(int|string $id): void
    {
        if ($this->synonyms !== null && $this->synonyms !== []) {
            $this->saveSynonyms(new Tag($id), $this->synonyms);
        }
    }

    #[\Override]
    protected function afterUpdate(): void
    {
        if ($this->synonyms !== null) {
            $this->removeSynonyms($this->object);

            if ($this->synonyms !== []) {
                $this->saveSynonyms($this->object, $this->synonyms);
            }
        }
    }

    private function removeSynonyms(Tag $tag): void
    {
        $sql = "UPDATE  wcf1_tag
                SET     synonymFor = ?
                WHERE   synonymFor = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            null,
            $tag->tagID,
        ]);
    }

    /**
     * @param list<string> $synonyms
     */
    private function saveSynonyms(Tag $tag, array $synonyms): void
    {
        foreach ($synonyms as $synonym) {
            $synonymObj = Tag::getTag($synonym, $tag->languageID);
            if ($synonymObj === null) {
                TagBuilder::forCreate()
                    ->setName($synonym)
                    ->setLanguageID($tag->languageID)
                    ->setSynonymFor($tag)
                    ->save();
            } else {
                TagBuilder::forUpdate($synonymObj)
                    ->setSynonymFor($tag)
                    ->save();
            }
        }
    }
}
