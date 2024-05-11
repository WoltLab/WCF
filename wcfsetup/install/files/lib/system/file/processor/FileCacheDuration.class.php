<?php

namespace wcf\system\file\processor;

/**
 * Specifies the maximum cache lifetime of a file in the browser.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class FileCacheDuration
{
    public static function shortLived(): self
    {
        return new self(5 * 60);
    }

    public static function oneYear(): self
    {
        return new self(365 * 86_400);
    }

    public static function doNotCache(): self
    {
        return new self(null);
    }

    public static function customDuration(int $seconds): self
    {
        if ($seconds < 1) {
            throw new \OutOfBoundsException('The custom duration must be a positive integer greater than zero.');
        }

        return new self($seconds);
    }

    public function allowCaching(): bool
    {
        return $this->lifetimeInSeconds !== null;
    }

    private function __construct(
        public readonly ?int $lifetimeInSeconds,
    ) {
    }
}
