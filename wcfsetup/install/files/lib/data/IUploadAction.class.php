<?php

namespace wcf\data;

/**
 * Every database object action supporting file upload has to implement this interface.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
interface IUploadAction
{
    /**
     * Validates the 'upload' action.
     */
    public function validateUpload();

    /**
     * Saves uploaded files and returns the data of the uploaded files.
     *
     * @return  array
     */
    public function upload();
}
