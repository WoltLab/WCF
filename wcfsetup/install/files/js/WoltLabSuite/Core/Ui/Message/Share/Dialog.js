/**
 * Shows the share dialog when clicking on the share button of a message.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../Dom/Traverse", "../../../Clipboard", "../../Notification", "../../../StringUtil", "../../../Dom/Change/Listener", "./Providers", "../../../Component/Dialog", "WoltLabSuite/Core/Language", "../../../Event/Handler"], function (require, exports, tslib_1, DomTraverse, Clipboard, UiNotification, StringUtil, Listener_1, Providers_1, Dialog_1, Language_1, EventHandler) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    DomTraverse = tslib_1.__importStar(DomTraverse);
    Clipboard = tslib_1.__importStar(Clipboard);
    UiNotification = tslib_1.__importStar(UiNotification);
    StringUtil = tslib_1.__importStar(StringUtil);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    EventHandler = tslib_1.__importStar(EventHandler);
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
        UiNotification.show((0, Language_1.getPhrase)("wcf.message.share.copy.success"));
    }
    /**
     * Returns all of the dialog elements shown in the dialog.
     */
    function getDialogElements(shareButton) {
        const permalink = getLink(shareButton);
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
        const link = getLink(shareButton);
        shareButton.dispatchEvent(event);
        if (event.defaultPrevented) {
            return payload.permalinkHtml;
        }
        if (link && shareButton.dataset.linkTitle) {
            return [
                [
                    "wcf.message.share.permalink.html",
                    `<a href="${StringUtil.escapeHTML(link)}">${StringUtil.escapeHTML(shareButton.dataset.linkTitle)}</a>`,
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
        const link = getLink(shareButton);
        shareButton.dispatchEvent(event);
        if (event.defaultPrevented) {
            return payload.bbcodes;
        }
        if (shareButton.dataset.bbcode) {
            return [["wcf.message.share.permalink.bbcode", shareButton.dataset.bbcode]];
        }
        else if (link && shareButton.dataset.linkTitle) {
            return [["wcf.message.share.permalink.bbcode", `[url='${link}']${shareButton.dataset.linkTitle}[/url]`]];
        }
        return [];
    }
    /**
     * Returns a dialog element with the given label and input field value.
     */
    function getDialogElement(label, value) {
        return `
    <dl>
      <dt>${(0, Language_1.getPhrase)(label)}</dt>
      <dd>
        <div class="inputAddon">
          <input type="text" class="long" readonly value="${StringUtil.escapeHTML(value)}">
          <button type="button" class="inputSuffix button jsTooltip shareDialogCopyButton" title="${(0, Language_1.getPhrase)("wcf.message.share.copy")}">
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
        const link = getLink(target);
        if (dialog === undefined) {
            const providerButtons = getProviderButtons();
            let providerElement = "";
            if (providerButtons) {
                providerElement = `
        <dl class="messageShareButtons jsMessageShareButtons" data-url="${StringUtil.escapeHTML(link)}">
          <dt>${(0, Language_1.getPhrase)("wcf.message.share.socialMedia")}</dt>
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
              <button type="button" class="button shareDialogNativeButton" data-url="${StringUtil.escapeHTML(link)}" data-title="${StringUtil.escapeHTML(target.dataset.linkTitle || "")}">
                ${(0, Language_1.getPhrase)("wcf.message.share.nativeShare")}
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
                initProviderButtons(dialog.content, link);
            }
        }
        dialog.show((0, Language_1.getPhrase)("wcf.message.share"));
    }
    function registerButtons() {
        document.querySelectorAll(".shareButton, .wsShareButton").forEach((shareButton) => {
            if (!shareButtons.has(shareButton)) {
                shareButton.addEventListener("click", (ev) => openDialog(ev));
                shareButtons.add(shareButton);
            }
        });
    }
    function getLink(button) {
        if (button instanceof HTMLAnchorElement) {
            return button.href;
        }
        return button.dataset.link;
    }
    function initProviderButtons(container, link) {
        const providers = {
            facebook: {
                selector: '.messageShareProvider[data-identifier="Facebook"]',
                share() {
                    share("facebook", "https://www.facebook.com/sharer.php?u={pageURL}&t={text}", true, link);
                },
            },
            reddit: {
                selector: '.messageShareProvider[data-identifier="Reddit"]',
                share() {
                    share("reddit", "https://ssl.reddit.com/submit?url={pageURL}", false, link);
                },
            },
            twitter: {
                selector: '.messageShareProvider[data-identifier="Twitter"]',
                share() {
                    share("twitter", "https://twitter.com/share?url={pageURL}&text={text}", false, link);
                },
            },
            linkedIn: {
                selector: '.messageShareProvider[data-identifier="LinkedIn"]',
                share() {
                    share("linkedIn", "https://www.linkedin.com/cws/share?url={pageURL}", false, link);
                },
            },
            pinterest: {
                selector: '.messageShareProvider[data-identifier="Pinterest"]',
                share() {
                    share("pinterest", "https://www.pinterest.com/pin/create/link/?url={pageURL}&description={text}", false, link);
                },
            },
            xing: {
                selector: '.messageShareProvider[data-identifier="XING"]',
                share() {
                    share("xing", "https://www.xing.com/social_plugins/share?url={pageURL}", false, link);
                },
            },
            whatsApp: {
                selector: '.messageShareProvider[data-identifier="WhatsApp"]',
                share() {
                    window.location.href = "https://api.whatsapp.com/send?text=" + getPageDescription() + "%20" + link;
                },
            },
        };
        EventHandler.fire("com.woltlab.wcf.message.share", "shareProvider", {
            container,
            providers,
            pageDescription: getPageDescription(),
            pageUrl: link,
        });
        Object.values(providers).forEach((provider) => {
            container.querySelector(provider.selector)?.addEventListener("click", () => provider.share());
        });
    }
    function share(objectName, url, appendUrl, pageUrl) {
        window.open(url.replace("{pageURL}", pageUrl).replace("{text}", getPageDescription() + (appendUrl ? `%20${pageUrl}` : "")), objectName, "height=600,width=600");
    }
    function getPageDescription() {
        const title = document.querySelector('meta[property="og:title"]');
        if (title !== null) {
            return encodeURIComponent(title.content);
        }
        return "";
    }
    function setup() {
        registerButtons();
        Listener_1.default.add("WoltLabSuite/Core/Ui/Message/Share/Dialog", () => registerButtons());
    }
    exports.setup = setup;
});
