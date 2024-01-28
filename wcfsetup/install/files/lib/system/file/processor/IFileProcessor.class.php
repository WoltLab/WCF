<?php

namespace wcf\system\file\processor;

/**
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
interface IFileProcessor
{
    public function acceptUpload(string $filename, int $fileSize, array $context): FileProcessorPreflightResult;

    public function getAllowedFileExtensions(array $context): array;

    public function getTypeName(): string;
}
