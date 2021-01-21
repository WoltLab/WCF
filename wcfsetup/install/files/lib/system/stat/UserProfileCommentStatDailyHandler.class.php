<?php

namespace wcf\system\stat;

/**
 * Stat handler implementation for user profile comments.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Stat
 */
class UserProfileCommentStatDailyHandler extends AbstractCommentStatDailyHandler
{
    /**
     * @inheritDoc
     */
    protected $objectType = 'com.woltlab.wcf.user.profileComment';
}
