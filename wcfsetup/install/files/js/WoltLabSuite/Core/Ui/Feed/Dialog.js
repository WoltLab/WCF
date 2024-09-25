/**
 * Shows a dialog with an anonymous and personalized link for RSS feeds with access tokens.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../Dialog", "../../StringUtil", "../../Language", "../../Clipboard", "../Notification"], function (require, exports, tslib_1, Dialog_1, StringUtil, Language, Clipboard, UiNotification) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    StringUtil = tslib_1.__importStar(StringUtil);
    Language = tslib_1.__importStar(Language);
    Clipboard = tslib_1.__importStar(Clipboard);
    UiNotification = tslib_1.__importStar(UiNotification);
    /**
     * Copies one of links to the clipboard.
     */
    async function copy(event) {
        event.preventDefault();
        const target = event.currentTarget;
        const input = target.parentNode.querySelector('input[type="text"]');
        await Clipboard.copyTextToClipboard(input.value);
        UiNotification.show(Language.get("wcf.global.rss.copy.success"));
    }
    /**
     * Opens the dialog with an anonymous and personalized link after clicking on the RSS feed link.
     */
    function openDialog(event) {
        event.preventDefault();
        const alternative = event.currentTarget;
        const linkWithAccessToken = alternative.href;
        const linkWithoutAccessToken = linkWithAccessToken.replace(/(\\?|&)at=[^&]*&?/, "$1").replace(/(\?|&)$/, "");
        Dialog_1.default.openStatic("feedLinkDialog", `
<woltlab-core-notice type="info">${Language.get("wcf.global.rss.accessToken.info")}</woltlab-core-notice>
<dl>
  <dt>${Language.get("wcf.global.rss.withoutAccessToken")}</dt>
  <dd>
    <div class="inputAddon">
      <input type="text" class="long" readonly value="${StringUtil.escapeHTML(linkWithoutAccessToken)}">
      <button type="button" class="inputSuffix button jsTooltip feedLinkDialogCopyButton" title="${Language.get("wcf.global.rss.copy")}">
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
      <button type="button" class="inputSuffix button jsTooltip feedLinkDialogCopyButton" title="${Language.get("wcf.global.rss.copy")}">
        <fa-icon name="copy"></fa-icon>
      </a>
    </div>
  </dd>
</dl>
`, {
            onShow(content) {
                content
                    .querySelectorAll(".feedLinkDialogCopyButton")
                    .forEach((el) => el.addEventListener("click", (ev) => copy(ev)));
            },
            title: alternative.title || Language.get("wcf.global.button.rss"),
        });
    }
    function setup() {
        document.querySelectorAll("a.rssFeed").forEach((link) => {
            link.addEventListener("click", (ev) => openDialog(ev));
        });
    }
});
