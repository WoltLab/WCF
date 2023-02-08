/**
 * Shows the share dialog when clicking on the share button of a message.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Message/Share/Dialog
 */
define(["require", "exports", "tslib", "../../../Dom/Traverse", "../../../Language", "../../../Clipboard", "../../Notification", "../../../StringUtil", "../../../Dom/Change/Listener", "../Share", "./Providers", "../../../Component/Dialog"], function (require, exports, tslib_1, DomTraverse, Language, Clipboard, UiNotification, StringUtil, Listener_1, UiMessageShare, Providers_1, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    DomTraverse = tslib_1.__importStar(DomTraverse);
    Language = tslib_1.__importStar(Language);
    Clipboard = tslib_1.__importStar(Clipboard);
    UiNotification = tslib_1.__importStar(UiNotification);
    StringUtil = tslib_1.__importStar(StringUtil);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    UiMessageShare = tslib_1.__importStar(UiMessageShare);
    const shareButtons = new WeakSet();
    const offerNativeSharing = window.navigator.share !== undefined;
    let dialog = undefined;
    /**
     * Copies the contents of one of the share dialog's input elements to the clipboard.
     */
    async function copy(event) {
        event.preventDefault();
        const target = event.currentTarget;
        const input = DomTraverse.prevBySel(target, 'input[type="text"]');
        await Clipboard.copyTextToClipboard(input.value);
        UiNotification.show(Language.get("wcf.message.share.copy.success"));
    }
    /**
     * Returns all of the dialog elements shown in the dialog.
     */
    function getDialogElements(shareButton) {
        const permalink = shareButton.href;
        let dialogOptions = getDialogElement("wcf.message.share.permalink", permalink);
        getBBCodes(shareButton).forEach(([label, value]) => {
            dialogOptions += getDialogElement(label, value);
        });
        getPermalinkHtml(shareButton).forEach(([label, value]) => {
            dialogOptions += getDialogElement(label, value);
        });
        return dialogOptions;
    }
    function getPermalinkHtml(shareButton) {
        const payload = {
            permalinkHtml: [],
        };
        const event = new CustomEvent("share:permalink-html", {
            cancelable: true,
            detail: payload,
        });
        shareButton.dispatchEvent(event);
        if (event.defaultPrevented) {
            return payload.permalinkHtml;
        }
        if (shareButton.href && shareButton.dataset.linkTitle) {
            return [
                [
                    "wcf.message.share.permalink.html",
                    `<a href="${StringUtil.escapeHTML(shareButton.href)}">${StringUtil.escapeHTML(shareButton.dataset.linkTitle)}</a>`,
                ],
            ];
        }
        return [];
    }
    function getBBCodes(shareButton) {
        const payload = {
            bbcodes: [],
        };
        const event = new CustomEvent("share:bbcodes", {
            cancelable: true,
            detail: payload,
        });
        shareButton.dispatchEvent(event);
        if (event.defaultPrevented) {
            return payload.bbcodes;
        }
        if (shareButton.dataset.bbcode) {
            return [["wcf.message.share.permalink.bbcode", shareButton.dataset.bbcode]];
        }
        else if (shareButton.href && shareButton.dataset.linkTitle) {
            return [
                ["wcf.message.share.permalink.bbcode", `[url='${shareButton.href}']${shareButton.dataset.linkTitle}[/url]`],
            ];
        }
        return [];
    }
    /**
     * Returns a dialog element with the given label and input field value.
     */
    function getDialogElement(label, value) {
        return `
    <dl>
      <dt>${Language.get(label)}</dt>
      <dd>
        <div class="inputAddon">
          <input type="text" class="long" readonly value="${StringUtil.escapeHTML(value)}">
          <button type="button" class="inputSuffix button jsTooltip shareDialogCopyButton" title="${Language.get("wcf.message.share.copy")}">
            <fa-icon name="copy"></fa-icon>
          </button>
        </div>
      </dd>
    </dl>
  `;
    }
    function getProviderButtons() {
        const providerButtons = Array.from((0, Providers_1.getShareProviders)())
            .map((provider) => {
            const [identifier, label, icon] = provider;
            return `
      <li>
        <button type="button" class="button small messageShareProvider" title="${label}" aria-label="${label}" data-identifier="${identifier}">
          ${icon}
          <span>${label}</span>
        </button>
      </li>
    `;
        })
            .join("\n");
        if (providerButtons) {
            return `<ul class="inlineList">${providerButtons}</ul>`;
        }
        return "";
    }
    /**
     * Opens the native share menu.
     */
    async function nativeShare(event) {
        event.preventDefault();
        const button = event.currentTarget;
        const shareOptions = {
            url: button.dataset.url,
        };
        if (button.dataset.title) {
            shareOptions.title = button.dataset.title;
        }
        await window.navigator.share(shareOptions);
    }
    /**
     * Opens the share dialog after clicking on the share button.
     */
    function openDialog(event) {
        event.preventDefault();
        const target = event.currentTarget;
        if (dialog === undefined) {
            const providerButtons = getProviderButtons();
            let providerElement = "";
            if (providerButtons) {
                providerElement = `
        <dl class="messageShareButtons jsMessageShareButtons" data-url="${StringUtil.escapeHTML(target.href)}">
          <dt>${Language.get("wcf.message.share.socialMedia")}</dt>
          <dd>${providerButtons}</dd>
        </dl>
      `;
            }
            let nativeSharingElement = "";
            if (offerNativeSharing) {
                nativeSharingElement = `
        <dl>
          <dt></dt>
          <dd>
              <button type="button" class="button shareDialogNativeButton" data-url="${StringUtil.escapeHTML(target.href)}" data-title="${StringUtil.escapeHTML(target.dataset.linkTitle || "")}">
                ${Language.get("wcf.message.share.nativeShare")}
              </button>
          </dd>
        </dl>
      `;
            }
            const dialogContent = `
      <div class="shareContentDialog">
        ${getDialogElements(target)}
        ${providerElement}
        ${nativeSharingElement}
      </div>
    `;
            dialog = (0, Dialog_1.dialogFactory)().fromHtml(dialogContent).withoutControls();
            dialog.content
                .querySelectorAll(".shareDialogCopyButton")
                .forEach((el) => el.addEventListener("click", (ev) => copy(ev)));
            if (offerNativeSharing) {
                dialog.content.querySelector(".shareDialogNativeButton").addEventListener("click", (ev) => nativeShare(ev));
            }
            if (providerButtons) {
                UiMessageShare.init();
            }
        }
        dialog.show(Language.get("wcf.message.share"));
    }
    function registerButtons() {
        document.querySelectorAll("a.shareButton, a.wsShareButton").forEach((shareButton) => {
            if (!shareButtons.has(shareButton)) {
                shareButton.addEventListener("click", (ev) => openDialog(ev));
                shareButtons.add(shareButton);
            }
        });
    }
    function setup() {
        registerButtons();
        Listener_1.default.add("WoltLabSuite/Core/Ui/Message/Share/Dialog", () => registerButtons());
    }
    exports.setup = setup;
});
