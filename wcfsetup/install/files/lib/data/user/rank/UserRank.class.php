<?php

namespace wcf\data\user\rank;

use wcf\data\CollectionDatabaseObject;
use wcf\data\file\File;
use wcf\data\ITitledObject;
use wcf\system\cache\runtime\FileRuntimeCache;
use wcf\system\l10n\L10nDefinition;
use wcf\system\l10n\L10nStorage;
use wcf\util\StringUtil;

/**
 * Represents a user rank.
 *
 * The localized title is stored in the `wcf1_user_rank_l10n` table.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $rankID             unique id of the user rank
 * @property-read   int     $groupID            id of the user group to which the user rank belongs
 * @property-read   int     $requiredPoints     minimum number of user activity points required for a user to get the user rank
 * @property-read   string  $cssClassName       css class name used when displaying the user rank
 * @property-read   ?int    $rankImageFileID    id of the file of the image displayed next to the rank or `null` if no rank image exists
 * @property-read   string  $rankImage          non-empty if a rank image exists, the value itself has no meaning (deprecated since 6.3, use `rankImageFileID` instead)
 * @property-read   int     $repeatImage        number of times the rank image is displayed
 * @property-read   int     $requiredGender     numeric representation of the user's gender required for the user rank (see `UserProfile::GENDER_*` constants) or 0 if no specific gender is required
 * @property-read   0|1     $hideTitle          hides the generic title of the rank, but not custom titles, `0` to show the title at all times
 * @property-read   ?string $l10nIdentifier     name of the language variable the localized title is derived from, `null` for user ranks created by an administrator
 *
 * @extends CollectionDatabaseObject<UserRankCollection>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
class UserRank extends CollectionDatabaseObject implements ITitledObject
{
    /**
     * Directory of the legacy rank images, only used to import them.
     */
    public const RANK_IMAGE_DIR = 'images/rank/';

    private ?File $imageFile = null;

    #[\Override]
    protected function handleData(array $data)
    {
        // `rankImage` was replaced by `rankImageFileID` in 6.3, but templates
        // commonly test it for the presence of a rank image.
        $data['rankImage'] = isset($data['rankImageFileID']) ? (string)$data['rankImageFileID'] : '';

        parent::handleData($data);
    }

    /**
     * Returns the image of this user rank.
     *
     * @return  string      html code
     */
    public function getImage()
    {
        $file = $this->getImageFile();
        if ($file === null) {
            return '';
        }

        $source = $file->getFullSizeImageSource() ?? $file->getLink();
        $image = '<img src="' . StringUtil::encodeHTML($source) . '" alt="">';
        if ($this->repeatImage > 1) {
            $image = \str_repeat($image, $this->repeatImage);
        }

        return $image;
    }

    /**
     * @since 6.3
     */
    public function getImageFile(): ?File
    {
        if ($this->rankImageFileID === null) {
            return null;
        }

        $this->imageFile ??= FileRuntimeCache::getInstance()->getObject($this->rankImageFileID);

        return $this->imageFile;
    }

    /**
     * @since 6.3
     */
    public function setImageFile(File $file): void
    {
        \assert($file->fileID === $this->rankImageFileID);

        $this->imageFile = $file;
    }

    /**
     * @since   5.2
     */
    #[\Override]
    public function getTitle(): string
    {
        return $this->getCollection()->getResolvedL10nValue($this, 'rankTitle');
    }

    /**
     * Returns the localized values of the given column as a
     * `languageID => value` map (see `L10nStorage`).
     *
     * @return L10nValue
     * @since 6.3
     */
    public function getL10nValues(string $columnName): array
    {
        if ($columnName !== 'rankTitle') {
            throw new \InvalidArgumentException("Invalid column name given.");
        }

        return $this->getCollection()->getL10nValues($this, $columnName);
    }

    /**
     * Returns true if the generic rank title should be displayed.
     *
     * @return      bool
     */
    public function showTitle()
    {
        return $this->rankImageFileID === null || $this->hideTitle === 0;
    }

    /**
     * @since 6.3
     */
    public static function getL10nDefinition(): L10nDefinition
    {
        return new L10nDefinition(
            'wcf1_user_rank',
            'wcf1_user_rank_l10n',
            'rankID',
            ['rankTitle'],
            'l10nIdentifier',
            ['rankTitle' => ''],
            ['rankTitle' => 255],
        );
    }
}
