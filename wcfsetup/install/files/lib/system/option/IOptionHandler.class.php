<?php

namespace wcf\system\option;

/**
 * Every option handler has to implement this interface.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IOptionHandler
{
    /**
     * Creates a new option handler instance.
     *
     * @param bool $supportI18n
     * @param string $languageItemPattern
     * @param string $categoryName
     */
    public function __construct($supportI18n, $languageItemPattern = '', $categoryName = '');

    /**
     * Reads user input from given source array.
     *
     * @param array $source
     */
    public function readUserInput(array &$source);

    /**
     * Validates user input, returns an array with all occurred errors.
     *
     * @return  array
     */
    public function validate();

    /**
     * Returns the tree of options.
     *
     * @param string $parentCategoryName
     * @param int $level
     * @return  array
     */
    public function getOptionTree($parentCategoryName = '', $level = 0);

    /**
     * Returns a list with the options of a specific option category.
     *
     * @param string $categoryName
     * @param bool $inherit
     * @return  array
     */
    public function getCategoryOptions($categoryName = '', $inherit = true);

    /**
     * Initializes i18n support.
     */
    public function readData();

    /**
     * Saves i18n variables and returns the updated option values.
     *
     * @param string $categoryName
     * @param string $optionPrefix
     * @return  array
     */
    public function save($categoryName = null, $optionPrefix = null);

    /**
     * Initializes active options.
     */
    public function init();
}
