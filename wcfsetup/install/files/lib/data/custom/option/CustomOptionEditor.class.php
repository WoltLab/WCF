<?php

namespace wcf\data\custom\option;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit file options.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       CustomOption
 * @template TCustomOption of CustomOption = CustomOption
 * @extends DatabaseObjectEditor<TCustomOption>
 * @deprecated 6.2 Use `IFormOption` instead
 */
abstract class CustomOptionEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = CustomOption::class;
}
