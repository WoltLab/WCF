/**
 * Shows the share dialog when clicking on the share button of a message.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Message/Share/Dialog
 */

import UiDialog from "../../Dialog";
import DomUtil from "../../../Dom/Util";
import * as DomTraverse from "../../../Dom/Traverse";
import * as Language from "../../../Language";
import * as Clipboard from "../../../Clipboard";
import * as UiNotification from "../../Notification";
import * as StringUtil from "../../../StringUtil";
import DomChangeListener from "../../../Dom/Change/Listener";
import * as UiMessageShare from "../Share";
import * as UiMessageShareProviders from "./Providers";

const shareButtons = new WeakSet<HTMLElement>();

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
function getDialogElements(shareButton: HTMLElement): string {
  let dialogOptions = "";

  let permalink = "";
  if (shareButton instanceof HTMLAnchorElement && shareButton.href) {
    permalink = shareButton.href;
  }

  if (permalink) {
    dialogOptions += getDialogElement("wcf.message.share.permalink", permalink);
  }
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
      '<a href="' + permalink + '">' + shareButton.dataset.linkTitle + "</a>",
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
          <a href="#" class="inputSuffix button jsTooltip shareDialogCopyButton" title="${Language.get(
            "wcf.message.share.copy",
          )}"><span class="icon icon16 fa-files-o pointer"></span></a>
        </div>
      </dd>
    </dl>
  `;
}

function getProviderButtons(): string {
  let providerButtons = "";

  UiMessageShareProviders.getEnabledProviders().forEach((provider) => {
    const label = Language.get(provider.label);

    providerButtons += `
      <li>
        <a href="#" role="button" class="button ${provider.cssClass}" title="${label}" aria-label="${label}">
          <span class="icon icon24 ${provider.iconClassName}"></span>
          <span>${label}</span>
        </a>
      </li>
    `;
  });

  if (providerButtons) {
    providerButtons = `<ul class="inlineList">${providerButtons}</ul>`;
  }

  return providerButtons;
}

/**
 * Opens the share dialog after clicking on the share button.
 */
function openDialog(event: MouseEvent): void {
  event.preventDefault();

  const target = event.currentTarget as HTMLElement;
  const dialogId = `shareContentDialog_${DomUtil.identify(target)}`;
  if (!UiDialog.getDialog(dialogId)) {
    const providerButtons = getProviderButtons();
    let providerElement = "";
    if (providerButtons) {
      providerElement = `
        <dl class="messageShareButtons jsMessageShareButtons">
          <dt>${Language.get("wcf.message.share.socialMedia")}</dt>
          <dd>${providerButtons}</dd>
        </dl>
      `;
    }

    const dialogContent = `
      <div class="shareContentDialog">
        ${getDialogElements(target)}
        ${providerElement}
      </div>
    `;

    const dialogData = UiDialog.openStatic(dialogId, dialogContent, {
      title: Language.get("wcf.message.share"),
    });

    dialogData.content.style.maxWidth = "600px";
    dialogData.dialog
      .querySelectorAll(".shareDialogCopyButton")
      .forEach((el) => el.addEventListener("click", (ev) => copy(ev)));

    if (providerButtons) {
      UiMessageShare.init();
    }
  } else {
    UiDialog.openStatic(dialogId, null);
  }
}

function registerButtons(): void {
  document.querySelectorAll(".shareButton").forEach((shareButton: HTMLElement) => {
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
