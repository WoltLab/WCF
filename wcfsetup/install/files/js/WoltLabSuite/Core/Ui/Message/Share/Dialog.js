/**
 * Shows the share dialog when clicking on the share button of a message.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Message/Share/Dialog
 */
define(["require", "exports", "tslib", "../../Dialog", "../../../Dom/Util", "../../../Dom/Traverse", "../../../Language", "../../../Clipboard", "../../Notification", "../../../StringUtil", "../../../Dom/Change/Listener", "../Share", "./Providers"], function (require, exports, tslib_1, Dialog_1, Util_1, DomTraverse, Language, Clipboard, UiNotification, StringUtil, Listener_1, UiMessageShare, UiMessageShareProviders) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    DomTraverse = tslib_1.__importStar(DomTraverse);
    Language = tslib_1.__importStar(Language);
    Clipboard = tslib_1.__importStar(Clipboard);
    UiNotification = tslib_1.__importStar(UiNotification);
    StringUtil = tslib_1.__importStar(StringUtil);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    UiMessageShare = tslib_1.__importStar(UiMessageShare);
    UiMessageShareProviders = tslib_1.__importStar(UiMessageShareProviders);
    const shareButtons = new WeakSet();
    const offerNativeSharing = window.navigator.share !== undefined;
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
        if (shareButton.dataset.bbcode) {
            dialogOptions += getDialogElement("wcf.message.share.permalink.bbcode", shareButton.dataset.bbcode);
        }
        if (permalink && shareButton.dataset.linkTitle) {
            if (!shareButton.dataset.bbcode) {
                dialogOptions += getDialogElement("wcf.message.share.permalink.bbcode", `[url='${permalink}']${shareButton.dataset.linkTitle}[/url]`);
            }
            dialogOptions += getDialogElement("wcf.message.share.permalink.html", `<a href="${StringUtil.escapeHTML(permalink)}">${StringUtil.escapeHTML(shareButton.dataset.linkTitle)}</a>`);
        }
        return dialogOptions;
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
          <a href="#" class="inputSuffix button jsTooltip shareDialogCopyButton" title="${Language.get("wcf.message.share.copy")}"><span class="icon icon16 fa-files-o pointer"></span></a>
        </div>
      </dd>
    </dl>
  `;
    }
    function getProviderButtons() {
        const providerButtons = Array.from(UiMessageShareProviders.getEnabledProviders())
            .map((provider) => {
            const label = Language.get(provider.label);
            return `
      <li>
        <a href="#" role="button" class="button ${provider.cssClass}" title="${label}" aria-label="${label}">
          <span class="icon icon24 ${provider.iconClassName}"></span>
          <span>${label}</span>
        </a>
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
        const dialogId = `shareContentDialog_${Util_1.default.identify(target)}`;
        if (!Dialog_1.default.getDialog(dialogId)) {
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
              <button class="shareDialogNativeButton" data-url="${StringUtil.escapeHTML(target.href)}" data-title="${StringUtil.escapeHTML(target.dataset.linkTitle || "")}">${Language.get("wcf.message.share.nativeShare")}</button>
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
            const dialogData = Dialog_1.default.openStatic(dialogId, dialogContent, {
                title: Language.get("wcf.message.share"),
            });
            dialogData.content.style.maxWidth = "600px";
            dialogData.content
                .querySelectorAll(".shareDialogCopyButton")
                .forEach((el) => el.addEventListener("click", (ev) => copy(ev)));
            if (offerNativeSharing) {
                dialogData.content.querySelector(".shareDialogNativeButton").addEventListener("click", (ev) => nativeShare(ev));
            }
            if (providerButtons) {
                UiMessageShare.init();
            }
        }
        else {
            Dialog_1.default.openStatic(dialogId, null);
        }
    }
    function registerButtons() {
        document.querySelectorAll("a.shareButton,a.wsShareButton").forEach((shareButton) => {
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
