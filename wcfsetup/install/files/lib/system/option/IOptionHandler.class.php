<?php

namespace wcf\system\option;

use wcf\data\option\Option;

/**
 * Every option handler has to implement this interface.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IOptionHandler
{
    public function __construct(bool $supportI18n, string $languageItemPattern = '', string $categoryName = '');

    /**
     * Reads user input from given source array.
     *
     * @param mixed[] $source
     * @return void
     */
    public function readUserInput(array &$source);

    /**
     * Validates user input, returns an array with all occurred errors.
     *
     * @return array<string, mixed>
     */
    public function validate();

    /**
     * Returns the tree of options.
     *
     * @return mixed[]
     */
    public function getOptionTree(string $parentCategoryName = '', int $level = 0);

    /**
     * Returns a list with the options of a specific option category.
     *
     * @return list<Option>
     */
    public function getCategoryOptions(string $categoryName = '', bool $inherit = true);

    /**
     * Initializes i18n support.
     *
     * @return void
     */
    public function readData();

    /**
     * Saves i18n variables and returns the updated option values.
     *
     * @return array<int, mixed>
     */
    public function save(?string $categoryName = null, ?string $optionPrefix = null);

    /**
     * Initializes active options.
     *
     * @return void
     */
    public function init();
}
