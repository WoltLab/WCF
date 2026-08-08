<?php

namespace wcf\system\upload;

/**
 * Upload file validation strategy implementation for media files.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class MediaUploadFileValidationStrategy implements IUploadFileValidationStrategy
{
    /**
     * if `true`, only images are valid
     * @var bool
     */
    protected $imagesOnly = false;

    public function __construct(bool $imagesOnly)
    {
        $this->imagesOnly = $imagesOnly;
    }

    #[\Override]
    public function validate(UploadFile $uploadFile)
    {
        if ($uploadFile->getErrorCode() !== 0) {
            $uploadFile->setValidationErrorType('uploadFailed');

            return false;
        }

        if ($this->imagesOnly && $uploadFile->getImageData() === null) {
            $uploadFile->setValidationErrorType('noImage');

            return false;
        }

        return true;
    }
}
