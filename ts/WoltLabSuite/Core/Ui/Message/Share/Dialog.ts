/**
 * Shows the share dialog when clicking on the share button of a message.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Message/Share/Dialog
 */

import * as DomTraverse from "../../../Dom/Traverse";
import * as Language from "../../../Language";
import * as Clipboard from "../../../Clipboard";
import * as UiNotification from "../../Notification";
import * as StringUtil from "../../../StringUtil";
import DomChangeListener from "../../../Dom/Change/Listener";
import * as UiMessageShare from "../Share";
import { getShareProviders } from "./Providers";
import { dialogFactory, WoltlabCoreDialogElement } from "../../../Dialog";

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

  UiNotification.show(Language.get("wcf.message.share.copy.success"));
}

/**
 * Returns all of the dialog elements shown in the dialog.
 */
function getDialogElements(shareButton: HTMLAnchorElement): string {
  const permalink = shareButton.href;

  let dialogOptions = getDialogElement("wcf.message.share.permalink", permalink);

  if (shareButton.dataset.bbcode) {
    dialogOptions += getDialogElement("wcf.message.share.permalink.bbcode", shareButton.dataset.bbcode);
  }
  if (permalink && shareButton.dataset.linkTitle) {
    if (!shareButton.dataset.bbcode) {
      dialogOptions += getDialogElement(
        "wcf.message.share.permalink.bbcode",
        `[url='${permalink}']${shareButton.dataset.linkTitle}[/url]`,
      );
    }

    dialogOptions += getDialogElement(
      "wcf.message.share.permalink.html",
      `<a href="${StringUtil.escapeHTML(permalink)}">${StringUtil.escapeHTML(shareButton.dataset.linkTitle)}</a>`,
    );
  }

  return dialogOptions;
}

/**
 * Returns a dialog element with the given label and input field value.
 */
function getDialogElement(label: string, value: string): string {
  return `
    <dl>
      <dt>${Language.get(label)}</dt>
      <dd>
        <div class="inputAddon">
          <input type="text" class="long" readonly value="${StringUtil.escapeHTML(value)}">
          <button type="button" class="inputSuffix button jsTooltip shareDialogCopyButton" title="${Language.get(
            "wcf.message.share.copy",
          )}">
            <fa-icon name="copy"></fa-icon>
          </a>
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

  const target = event.currentTarget as HTMLAnchorElement;
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
              <button type="button" class="button shareDialogNativeButton" data-url="${StringUtil.escapeHTML(
                target.href,
              )}" data-title="${StringUtil.escapeHTML(target.dataset.linkTitle || "")}">
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

  dialog.show(Language.get("wcf.message.share"));
}

function registerButtons(): void {
  document.querySelectorAll("a.shareButton, a.wsShareButton").forEach((shareButton: HTMLElement) => {
    if (!shareButtons.has(shareButton)) {
      shareButton.addEventListener("click", (ev) => openDialog(ev));

      shareButtons.add(shareButton);
    }
  });
}

export function setup(): void {
  registerButtons();
  DomChangeListener.add("WoltLabSuite/Core/Ui/Message/Share/Dialog", () => registerButtons());
}
