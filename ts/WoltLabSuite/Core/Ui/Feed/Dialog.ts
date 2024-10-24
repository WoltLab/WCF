/**
 * Shows a dialog with an anonymous and personalized link for RSS feeds with access tokens.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import UiDialog from "../Dialog";
import * as StringUtil from "../../StringUtil";
import * as Language from "../../Language";
import * as Clipboard from "../../Clipboard";
import * as UiNotification from "../Notification";

/**
 * Copies one of links to the clipboard.
 */
async function copy(event: Event): Promise<void> {
  event.preventDefault();

  const target = event.currentTarget as HTMLElement;
  const input = target.parentNode!.querySelector('input[type="text"]') as HTMLInputElement;

  await Clipboard.copyTextToClipboard(input.value);

  UiNotification.show(Language.get("wcf.global.rss.copy.success"));
}

/**
 * Opens the dialog with an anonymous and personalized link after clicking on the RSS feed link.
 */
function openDialog(event: Event): void {
  event.preventDefault();

  const alternative = event.currentTarget as HTMLAnchorElement;
  const linkWithAccessToken = alternative.href;

  const linkWithoutAccessToken = linkWithAccessToken.replace(/(\\?|&)at=[^&]*&?/, "$1").replace(/(\?|&)$/, "");

  UiDialog.openStatic(
    "feedLinkDialog",
    `
<p class="info">${Language.get("wcf.global.rss.accessToken.info")}</p>
<dl>
  <dt>${Language.get("wcf.global.rss.withoutAccessToken")}</dt>
  <dd>
    <div class="inputAddon">
      <input type="text" class="long" readonly value="${StringUtil.escapeHTML(linkWithoutAccessToken)}">
      <button type="button" class="inputSuffix button jsTooltip feedLinkDialogCopyButton" title="${Language.get(
        "wcf.global.rss.copy",
      )}">
        <fa-icon name="copy"></fa-icon>
      </button>
    </div>
  </dd>
</dl>
<dl>
  <dt>${Language.get("wcf.global.rss.withAccessToken")}</dt>
  <dd>
    <div class="inputAddon">
      <input type="text" class="long" readonly value="${StringUtil.escapeHTML(linkWithAccessToken)}">
      <button type="button" class="inputSuffix button jsTooltip feedLinkDialogCopyButton" title="${Language.get(
        "wcf.global.rss.copy",
      )}">
        <fa-icon name="copy"></fa-icon>
      </a>
    </div>
  </dd>
</dl>
`,
    {
      onShow(content: HTMLElement) {
        content
          .querySelectorAll(".feedLinkDialogCopyButton")
          .forEach((el) => el.addEventListener("click", (ev) => copy(ev)));
      },
      title: alternative.title || Language.get("wcf.global.button.rss"),
    },
  );
}

export function setup(): void {
  document.querySelectorAll("a.rssFeed").forEach((link: HTMLAnchorElement) => {
    link.addEventListener("click", (ev) => openDialog(ev));
  });
}
