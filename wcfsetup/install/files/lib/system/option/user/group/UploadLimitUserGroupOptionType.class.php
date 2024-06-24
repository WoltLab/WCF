<?php

namespace wcf\system\option\user\group;

use wcf\data\option\Option;
use wcf\data\user\group\option\UserGroupOption;
use wcf\system\exception\UserInputException;
use wcf\system\file\processor\FileProcessor;

/**
 * Option type implementation for limiting the size of uploaded files.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class UploadLimitUserGroupOptionType extends FileSizeUserGroupOptionType
{
    #[\Override]
    public function validate(Option $option, $newValue)
    {
        $newValue = $this->getContent($option, $newValue);

        if ($newValue < 1) {
            throw new UserInputException($option->optionName);
        }

        $maximumFileSize = FileProcessor::getInstance()->getMaximumFileSize();
        if ($newValue > $maximumFileSize) {
            \assert($option instanceof UserGroupOption);
            $option->addAdditionalData('maximumUploadLimit', $maximumFileSize);

            throw new UserInputException($option->optionName, 'maximumUploadLimit');
        }
    }
}
