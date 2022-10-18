/**
 * Updates the user's timezone option on the server if the browser's
 * timezone differs.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Controller\User\Profile
 */

import { getXsrfToken } from "../../../Core";

function getServerTimezone(): string | undefined {
  const meta = document.querySelector<HTMLMetaElement>('meta[name="timezone"]');

  return meta?.content;
}

function getClientTimezone(): string {
  return Intl.DateTimeFormat().resolvedOptions().timeZone;
}

export async function maybeSyncServerTimezone(): Promise<void> {
  if (getServerTimezone() !== getClientTimezone()) {
    try {
      await fetch(`${window.WSC_API_URL}index.php?user-timezone-sync/`, {
        method: "POST",
        headers: {
          "content-type": "application/json; charset=UTF-8",
          "x-xsrf-token": getXsrfToken(),
        },
        body: JSON.stringify({ tz: getClientTimezone() }),
      });
    } catch {
      // Ignore
    }
  }
}
