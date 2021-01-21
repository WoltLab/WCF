<?php

namespace wcf\data\bbcode\media\provider;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\data\TDatabaseObjectToggle;

/**
 * Executes BBCode media provider-related actions.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Bbcode\Media\Provider
 *
 * @method  BBCodeMediaProvider     create()
 * @method  BBCodeMediaProviderEditor[] getObjects()
 * @method  BBCodeMediaProviderEditor   getSingleObject()
 */
class BBCodeMediaProviderAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    use TDatabaseObjectToggle;

    /**
     * @inheritDoc
     */
    protected $className = BBCodeMediaProviderEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.content.bbcode.canManageBBCode'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.content.bbcode.canManageBBCode'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['delete', 'update'];
}
