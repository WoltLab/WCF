<?php

namespace wcf\system\upload;

/**
 * Default implementation of a file validation strategy for uploaded files.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class DefaultUploadFileValidationStrategy implements IUploadFileValidationStrategy
{
    /**
     * allowed max size
     * @var int
     */
    protected $maxFilesize = 0;

    /**
     * allowed file extensions
     * @var string[]
     */
    protected $fileExtensions = [];

    /**
     * regex for validation of allowed file extension
     * @var string
     */
    protected $fileExtensionRegex = '';

    /**
     * Creates a new DefaultUploadFileValidationStrategy object.
     *
     * @param int $maxFilesize
     * @param string[] $fileExtensions
     */
    public function __construct($maxFilesize, array $fileExtensions)
    {
        $this->maxFilesize = $maxFilesize;
        $this->fileExtensions = $fileExtensions;
        $extensions = \str_replace(
            "\n",
            "|",
            \str_replace('\*', '.*', \preg_quote(\implode("\n", $fileExtensions), '/'))
        );
        $this->fileExtensionRegex = '/(' . $extensions . ')$/i';
    }

    /**
     * @inheritDoc
     */
    public function validate(UploadFile $uploadFile)
    {
        if ($uploadFile->getErrorCode() != 0) {
            $additionalData = [];
            if ($uploadFile->getErrorCode() === \UPLOAD_ERR_INI_SIZE) {
                $additionalData['phpLimitExceeded'] = true;
            }

            $uploadFile->setValidationErrorType('uploadFailed', $additionalData);

            return false;
        }

        if (!\preg_match($this->fileExtensionRegex, \mb_strtolower($uploadFile->getFilename()))) {
            $uploadFile->setValidationErrorType('invalidExtension');

            return false;
        }

        if ($uploadFile->getFilesize() > $this->maxFilesize) {
            $uploadFile->setValidationErrorType('tooLarge');

            return false;
        }

        return true;
    }
}
