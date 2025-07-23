<?php

namespace wcf\data\custom\option;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\IToggleAction;
use wcf\data\TDatabaseObjectToggle;

/**
 * Executes option-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 * @deprecated 6.2 Use `IFormOption` instead
 *
 * @template TCustomOption of CustomOption = CustomOption
 * @template TCustomOptionEditor of CustomOptionEditor|DatabaseObjectDecorator<TCustomOption> = CustomOptionEditor
 * @extends AbstractDatabaseObjectAction<TCustomOption, TCustomOptionEditor>
 * @phpstan-ignore generics.notSubtype
 */
abstract class CustomOptionAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    use TDatabaseObjectToggle;

    /**
     * @inheritDoc
     */
    protected $className = CustomOptionEditor::class;

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'update'];
}
