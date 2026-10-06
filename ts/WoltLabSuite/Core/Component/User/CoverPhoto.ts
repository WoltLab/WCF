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

function getDefaultCoverPhoto(): string {
  return document.querySelector<HTMLElement>("[data-default-cover-photo]")?.dataset.defaultCoverPhoto ?? "";
}

function updateAcpCoverPhotoPreview(fileId: number | undefined, fileUrl: string): void {
  const coverPhotoPreview = document.getElementById("coverPhotoPreview");
  const coverPhotoNotice = document.getElementById("coverPhotoNotice");

  if (fileUrl) {
    const coverPhotoStyle = `url("${fileUrl}")`;

    if (coverPhotoPreview) {
      coverPhotoPreview.style.backgroundImage = coverPhotoStyle;
      coverPhotoPreview.dataset.objectId = fileId?.toString() || "";
    } else if (coverPhotoNotice) {
      coverPhotoNotice.parentElement!.appendChild(
        DomUtil.createFragmentFromHtml(
          `<div id="coverPhotoPreview" data-object-id="${fileId}" style="background-image: ${escapeHTML(coverPhotoStyle)};"></div>`,
        ),
      );
      coverPhotoNotice.remove();
    }
  } else if (coverPhotoPreview) {
    coverPhotoPreview.parentElement!.appendChild(
      DomUtil.createFragmentFromHtml(
        `<woltlab-core-notice id="coverPhotoNotice" type="info">${getPhrase("wcf.user.coverPhoto.noImage")}</woltlab-core-notice>`,
      ),
    );
    coverPhotoPreview.remove();
  }
}

export function setup(): void {
  registerCallback("wcf\\action\\UserCoverPhotoAction_coverPhotoFileID", (fileId: number | undefined) => {
    const headerImage = document.querySelector<HTMLImageElement>(".userProfileHeader__coverPhotoImage");
    const coverPhotoElement = headerImage ?? document.getElementById("coverPhotoPreview");

    if (coverPhotoElement && parseInt(coverPhotoElement.dataset.objectId!) === fileId) {
      // nothing changed
      return;
    }

    const file = document.querySelector<WoltlabCoreFile>(
      `#wcf\\\\action\\\\UserCoverPhotoAction_coverPhotoFileIDContainer woltlab-core-file[file-id="${fileId}"]`,
    );
    const fileUrl = file?.link ?? "";
    const coverPhotoUrl = fileUrl || getDefaultCoverPhoto();

    if (headerImage && coverPhotoUrl) {
      headerImage.src = coverPhotoUrl;
      headerImage.dataset.objectId = fileId?.toString() || "";

      document
        .getElementById("wcf\\action\\UserCoverPhotoAction_coverPhotoFileIDContainer")
        ?.closest("woltlab-core-dialog")
        ?.close();
    }

    // ACP cover photo management
    updateAcpCoverPhotoPreview(fileId, fileUrl);

    showDefaultSuccessSnackbar();
    fireEvent("com.woltlab.wcf.user", "coverPhoto", {
      url: coverPhotoUrl,
    });
  });

  wheneverFirstSeen("[data-edit-cover-photo]", (button) => {
    button.addEventListener(
      "click",
      promiseMutex(() => editCoverPhoto(button)),
    );
  });
}
