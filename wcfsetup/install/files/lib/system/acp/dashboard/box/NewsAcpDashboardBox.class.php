<?php

namespace wcf\system\acp\dashboard\box;

use wcf\system\WCF;

/**
 * ACP dashboard box that shows WoltLab news.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class NewsAcpDashboardBox extends AbstractAcpDashboardBox
{
    #[\Override]
    public function isAccessible(): bool
    {
        return ENABLE_WOLTLAB_NEWS;
    }

    public function getTitle(): string
    {
        return WCF::getLanguage()->get('wcf.acp.dashboard.box.news');
    }

    public function getContent(): string
    {
        return WCF::getTPL()->fetch('newsAcpDashboardBox');
    }

    public function getName(): string
    {
        return 'com.woltlab.wcf.news';
    }
}
