<?php

namespace wcf\system\acp\dashboard\box;

use wcf\system\WCF;

/**
 * ACP dashboard box that shows credits.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class CreditsAcpDashboardBox extends AbstractAcpDashboardBox
{
    public function getTitle(): string
    {
        return WCF::getLanguage()->get('wcf.acp.dashboard.box.credits');
    }

    public function getContent(): string
    {
        return WCF::getTPL()->fetch('creditsAcpDashboardBox');
    }

    public function getName(): string
    {
        return 'com.woltlab.wcf.credits';
    }
}
