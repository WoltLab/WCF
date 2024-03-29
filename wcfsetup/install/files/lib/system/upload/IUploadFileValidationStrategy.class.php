<?php

namespace wcf\system\upload;

/**
 * Interface for file upload validation strategies.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IUploadFileValidationStrategy
{
    /**
     * Validates the given file and returns true on success, otherwise false.
     *
     * @param UploadFile $uploadFile
     * @return  bool
     */
    public function validate(UploadFile $uploadFile);
}
