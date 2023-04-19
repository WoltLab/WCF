/**
 * Shows the share dialog when clicking on the share button of a message.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import * as DomTraverse from "../../../Dom/Traverse";
import * as Clipboard from "../../../Clipboard";
import * as UiNotification from "../../Notification";
import * as StringUtil from "../../../StringUtil";
import DomChangeListener from "../../../Dom/Change/Listener";
import * as UiMessageShare from "../Share";
import { getShareProviders } from "./Providers";
import { dialogFactory } from "../../../Component/Dialog";
import WoltlabCoreDialogElement from "../../../Element/woltlab-core-dialog";
import { getPhrase } from "WoltLabSuite/Core/Language";

type Label = string;
type Value = string;
type DialogElement = [Label, Value];

const shareButtons = new WeakSet<HTMLElement>();

const offerNativeSharing = window.navigator.share !== undefined;

let dialog: WoltlabCoreDialogElement | undefined = undefined;

/**
 * Copies the contents of one of the share dialog's input elements to the clipboard.
 */
async function copy(event: Event): Promise<void> {
  event.preventDefault();

  const target = event.currentTarget as HTMLElement;
  const input = DomTraverse.prevBySel(target, 'input[type="text"]') as HTMLInputElement;

  await Clipboard.copyTextToClipboard(input.value);

  UiNotification.show(getPhrase("wcf.message.share.copy.success"));
}

/**
 * Returns all of the dialog elements shown in the dialog.
 */
function getDialogElements(shareButton: HTMLElement): string {
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

function getPermalinkHtml(shareButton: HTMLElement): DialogElement[] {
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

function getBBCodes(shareButton: HTMLElement): DialogElement[] {
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
  } else if (link && shareButton.dataset.linkTitle) {
    return [["wcf.message.share.permalink.bbcode", `[url='${link}']${shareButton.dataset.linkTitle}[/url]`]];
  }

  return [];
}

/**
 * Returns a dialog element with the given label and input field value.
 */
function getDialogElement(label: string, value: string): string {
  return `
    <dl>
      <dt>${getPhrase(label)}</dt>
      <dd>
        <div class="inputAddon">
          <input type="text" class="long" readonly value="${StringUtil.escapeHTML(value)}">
          <button type="button" class="inputSuffix button jsTooltip shareDialogCopyButton" title="${getPhrase(
            "wcf.message.share.copy",
          )}">
            <fa-icon name="copy"></fa-icon>
          </button>
        </div>
      </dd>
    </dl>
  `;
}

function getProviderButtons(): string {
  const providerButtons = Array.from(getShareProviders())
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
async function nativeShare(event: Event): Promise<void> {
  event.preventDefault();

  const button = event.currentTarget as HTMLButtonElement;

  const shareOptions: ShareData = {
    url: button.dataset.url!,
  };
  if (button.dataset.title) {
    shareOptions.title = button.dataset.title;
  }

  await window.navigator.share(shareOptions);
}

/**
 * Opens the share dialog after clicking on the share button.
 */
function openDialog(event: MouseEvent): void {
  event.preventDefault();

  const target = event.currentTarget as HTMLElement;
  const link = getLink(target);
  if (dialog === undefined) {
    const providerButtons = getProviderButtons();
    let providerElement = "";
    if (providerButtons) {
      providerElement = `
        <dl class="messageShareButtons jsMessageShareButtons" data-url="${StringUtil.escapeHTML(link)}">
          <dt>${getPhrase("wcf.message.share.socialMedia")}</dt>
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
              <button type="button" class="button shareDialogNativeButton" data-url="${StringUtil.escapeHTML(
                link,
              )}" data-title="${StringUtil.escapeHTML(target.dataset.linkTitle || "")}">
                ${getPhrase("wcf.message.share.nativeShare")}
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

    dialog = dialogFactory().fromHtml(dialogContent).withoutControls();

    dialog.content
      .querySelectorAll(".shareDialogCopyButton")
      .forEach((el) => el.addEventListener("click", (ev) => copy(ev)));
    if (offerNativeSharing) {
      dialog.content.querySelector(".shareDialogNativeButton")!.addEventListener("click", (ev) => nativeShare(ev));
    }

    if (providerButtons) {
      UiMessageShare.init();
    }
  }

  dialog.show(getPhrase("wcf.message.share"));
}

function registerButtons(): void {
  document.querySelectorAll(".shareButton, .wsShareButton").forEach((shareButton: HTMLElement) => {
    if (!shareButtons.has(shareButton)) {
      shareButton.addEventListener("click", (ev) => openDialog(ev));

      shareButtons.add(shareButton);
    }
  });
}

function getLink(button: HTMLElement): string {
  if (button instanceof HTMLAnchorElement) {
    return button.href;
  }

  return button.dataset.link!;
}

export function setup(): void {
  registerButtons();
  DomChangeListener.add("WoltLabSuite/Core/Ui/Message/Share/Dialog", () => registerButtons());
}
