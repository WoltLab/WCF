<?php

namespace wcf\data\acp\search\provider;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes ACP search provider-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<ACPSearchProvider, ACPSearchProviderEditor>
 * @deprecated 6.3 ACP search providers are registered through the
 *             `wcf\event\acp\search\provider\ProviderCollecting` event. The
 *             search itself is served by the `/core/acp/search` RPC endpoint.
 */
class ACPSearchProviderAction extends AbstractDatabaseObjectAction {}
