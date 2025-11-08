/**
 * Handles the user cover photo edit buttons.
 *
 * @author    Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.2
 * @woltlabExcludeBundle all
 */

import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import * as FormBuilderManager from "WoltLabSuite/Core/Form/Builder/Manager";
import { fire as fireEvent } from "WoltLabSuite/Core/Event/Handler";
import { getPhrase } from "WoltLabSuite/Core/Language";
import DomUtil from "WoltLabSuite/Core/Dom/Util";
import { escapeHTML } from "WoltLabSuite/Core/StringUtil";
import { registerCallback } from "WoltLabSuite/Core/Form/Builder/Field/Controller/FileProcessor";
import WoltlabCoreFile from "WoltLabSuite/Core/Component/File/woltlab-core-file";
import { showDefaultSuccessSnackbar } from "../Snackbar";

type ResponseGetForm = {
  dialog: string;
  formId: string;
  title: string;
};

async function editCoverPhoto(button: HTMLElement): Promise<void> {
  const json = (await prepareRequest(button.dataset.editCoverPhoto!).get().fetchAsJson()) as ResponseGetForm;
  const dialog = dialogFactory().fromHtml(json.dialog).withoutControls();

  dialog.addEventListener("afterClose", () => {
    if (FormBuilderManager.hasForm(json.formId)) {
      FormBuilderManager.unregisterForm(json.formId);
    }
  });

  dialog.show(json.title);
}

function getCoverPhotoElement(): HTMLElement | null {
  return (
    document.querySelector<HTMLElement>(".userProfileHeader__coverPhotoImage") ??
    document.getElementById("coverPhotoPreview")
  );
}

export function setup(): void {
  wheneverFirstSeen("[data-edit-cover-photo]", (button) => {
    const defaultCoverPhoto = button.dataset.defaultCoverPhoto;

    registerCallback("wcf\\action\\UserCoverPhotoAction_coverPhotoFileID", (fileId: number | undefined) => {
      const coverPhotoElement = getCoverPhotoElement();

      if (coverPhotoElement && parseInt(coverPhotoElement.dataset.objectId!) === fileId) {
        // nothing changed
        return;
      }

      const file = document.querySelector<WoltlabCoreFile>(
        `#wcf\\\\action\\\\UserCoverPhotoAction_coverPhotoFileIDContainer woltlab-core-file[file-id="${fileId}"]`,
      );
      const coverPhotoNotice = document.getElementById("coverPhotoNotice");
      const coverPhotoUrl = file?.link ?? defaultCoverPhoto ?? "";
      const coverPhotoStyle = `url("${coverPhotoUrl}")`;

      if (coverPhotoElement instanceof HTMLImageElement && coverPhotoUrl) {
        coverPhotoElement.src = coverPhotoUrl;

        coverPhotoElement.dataset.objectId = fileId?.toString() || "";

        document
          .getElementById("wcf\\action\\UserCoverPhotoAction_coverPhotoFileIDContainer")
          ?.closest("woltlab-core-dialog")
          ?.close();
      } else {
        // ACP cover photo management
        if (!coverPhotoElement && coverPhotoUrl) {
          coverPhotoNotice!.parentElement!.appendChild(
            DomUtil.createFragmentFromHtml(
              `<div id="coverPhotoPreview" data-object-id="${fileId}" style="background-image: ${escapeHTML(coverPhotoStyle)};"></div>`,
            ),
          );
          coverPhotoNotice!.remove();
        } else if (coverPhotoElement && !coverPhotoUrl) {
          coverPhotoElement.parentElement!.appendChild(
            DomUtil.createFragmentFromHtml(
              `<woltlab-core-notice id="coverPhotoNotice" type="info">${getPhrase("wcf.user.coverPhoto.noImage")}</woltlab-core-notice>`,
            ),
          );
          coverPhotoElement.remove();
        } else if (coverPhotoElement && coverPhotoUrl) {
          coverPhotoElement.style.backgroundImage = coverPhotoStyle;
          coverPhotoElement.dataset.objectId = fileId?.toString() || "";
        }
      }

      showDefaultSuccessSnackbar();
      fireEvent("com.woltlab.wcf.user", "coverPhoto", {
        url: coverPhotoUrl,
      });
    });

    button.addEventListener(
      "click",
      promiseMutex(() => editCoverPhoto(button)),
    );
  });
}
