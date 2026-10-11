<?php

namespace wcf\system\view\user\profile;

use wcf\data\user\option\UserOption;
use wcf\data\user\UserProfile;
use wcf\system\option\user\TUserOptionsByCategory;

/**
 * Represents the view for the "About" tab of the user profile.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserProfileAboutView
{
    use TUserOptionsByCategory;

    /**
     * @var array<string, UserOption[]>
     */
    private array $categories;

    public function __construct(
        public readonly UserProfile $user
    ) {}

    /**
     * Returns the user options that are visible to the active user, grouped
     * by the name of their category.
     *
     * @return array<string, UserOption[]>
     */
    private function getCategories(): array
    {
        if (!isset($this->categories)) {
            $this->categories = $this->getUserOptionsByCategory('profile', function (UserOption $option) {
                if (!$option->validateOptions() || !$option->validatePermissions()) {
                    return false;
                }

                return $option->isVisible($this->user->getDecoratedObject());
            });
        }

        return $this->categories;
    }

    /**
     * Returns the localized title of the category with the given name.
     */
    public function getCategoryTitle(string $categoryName): string
    {
        return $this->getUserOptionCategoryTitle($categoryName);
    }

    /**
     * Returns the formatted values of the options grouped by the name of their
     * category, options without a value and categories without any formatted
     * value are skipped.
     *
     * @return array<string, list<array{
     *  object: UserOption,
     *  output: string,
     * }>>
     */
    public function getOptionValuesOutput(): array
    {
        $output = [];
        foreach ($this->getCategories() as $categoryName => $options) {
            $categoryOutput = $this->getCategoryOptionValuesOutput($options);
            if ($categoryOutput !== []) {
                $output[$categoryName] = $categoryOutput;
            }
        }

        return $output;
    }

    /**
     * @param UserOption[] $options
     * @return list<array{
     *  object: UserOption,
     *  output: string,
     * }>
     */
    private function getCategoryOptionValuesOutput(array $options): array
    {
        $output = [];
        foreach ($options as $option) {
            $value = $this->user->{$option->getIdentifier()};
            if ($value === null || !(bool)$value || $value === '0000-00-00') {
                continue;
            }

            $formattedValue = $option->getFormattedValue((string)$value, $this->user->getDecoratedObject());

            if ($formattedValue === '') {
                continue;
            }

            $output[] = [
                'object' => $option,
                'output' => $formattedValue,
            ];
        }

        return $output;
    }
}
