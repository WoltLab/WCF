<?php

namespace wcf\data\user\rank;

use wcf\data\DatabaseObject;
use wcf\data\file\File;
use wcf\data\ITitledObject;
use wcf\system\cache\runtime\FileRuntimeCache;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a user rank.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $rankID             unique id of the user rank
 * @property-read   int     $groupID            id of the user group to which the user rank belongs
 * @property-read   int     $requiredPoints     minimum number of user activity points required for a user to get the user rank
 * @property-read   string  $rankTitle          title of the user rank or name of the language item which contains the rank
 * @property-read   string  $cssClassName       css class name used when displaying the user rank
 * @property-read   ?int    $rankImageFileID    id of the file of the image displayed next to the rank or `null` if no rank image exists
 * @property-read   string  $rankImage          non-empty if a rank image exists, the value itself has no meaning (deprecated since 6.3, use `rankImageFileID` instead)
 * @property-read   int     $repeatImage        number of times the rank image is displayed
 * @property-read   int     $requiredGender     numeric representation of the user's gender required for the user rank (see `UserProfile::GENDER_*` constants) or 0 if no specific gender is required
 * @property-read   0|1     $hideTitle          hides the generic title of the rank, but not custom titles, `0` to show the title at all times
 */
class UserRank extends DatabaseObject implements ITitledObject
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
        return WCF::getLanguage()->get($this->rankTitle);
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
}
