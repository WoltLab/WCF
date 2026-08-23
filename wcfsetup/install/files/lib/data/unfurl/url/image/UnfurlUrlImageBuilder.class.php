<?php

namespace wcf\data\unfurl\url\image;

use wcf\data\DatabaseObjectBuilder;
use wcf\data\file\FileEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Builder for creating, updating and deleting unfurl url images.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<UnfurlUrlImage>
 */
final class UnfurlUrlImageBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the url of the image and derives the hash used to identify it.
     */
    public function setImageUrl(string $imageUrl): static
    {
        $this->properties['imageUrl'] = $imageUrl;
        $this->properties['imageUrlHash'] = \sha1($imageUrl);

        return $this;
    }

    public function setWidth(int $width): static
    {
        $this->properties['width'] = $width;

        return $this;
    }

    public function setHeight(int $height): static
    {
        $this->properties['height'] = $height;

        return $this;
    }

    public function setImageExtension(?string $imageExtension): static
    {
        $this->properties['imageExtension'] = $imageExtension;

        return $this;
    }

    public function setFileID(?int $fileID): static
    {
        $this->properties['fileID'] = $fileID;
        $this->properties['isStored'] = $fileID !== null ? 1 : 0;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['imageUrl', 'width', 'height'];
    }

    #[\Override]
    protected static function beforeDeleteAll(array $objectIDs): void
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('imageID IN (?)', [$objectIDs]);
        $conditionBuilder->add('fileID IS NOT NULL');

        $sql = "SELECT  fileID
                FROM    wcf1_unfurl_url_image
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
        $fileIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if ($fileIDs !== []) {
            FileEditor::deleteAll($fileIDs);
        }
    }
}
