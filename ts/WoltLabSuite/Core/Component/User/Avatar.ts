/**
 * Handles the user avatar edit buttons.
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
import { registerCallback } from "WoltLabSuite/Core/Form/Builder/Field/Controller/FileProcessor";
import WoltlabCoreFile from "WoltLabSuite/Core/Component/File/woltlab-core-file";
import { showDefaultSuccessSnackbar } from "../Snackbar";
import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import * as FormBuilderManager from "WoltLabSuite/Core/Form/Builder/Manager";

type ResponseGetForm = {
  dialog: string;
  formId: string;
  title: string;
};

let defaultAvatar = "";

async function editAvatar(button: HTMLElement): Promise<void> {
  defaultAvatar = button.dataset.defaultAvatar || "";

  const json = (await prepareRequest(button.dataset.editAvatar!).get().fetchAsJson()) as ResponseGetForm;
  const dialog = dialogFactory().fromHtml(json.dialog).withoutControls();

  dialog.addEventListener("afterClose", () => {
    if (FormBuilderManager.hasForm(json.formId)) {
      FormBuilderManager.unregisterForm(json.formId);
    }
  });

  dialog.show(json.title);
}

export function setup(): void {
  wheneverFirstSeen(
    `#wcf\\\\action\\\\UserAvatarAction_avatarFileIDContainer woltlab-core-file img`,
    (img: HTMLImageElement) => {
      img.classList.add("userAvatarImage");
      img.parentElement!.classList.add("userAvatar");
    },
  );

  const avatarForm = document.getElementById("avatarForm");
  if (avatarForm) {
    registerCallback("wcf\\action\\UserAvatarAction_avatarFileID", (fileId: number | undefined) => {
      let link = defaultAvatar;
      if (fileId !== undefined) {
        const file = document.querySelector<WoltlabCoreFile>(
          `#wcf\\\\action\\\\UserAvatarAction_avatarFileIDContainer woltlab-core-file[file-id="${fileId}"]`,
        )!;

        link = file.link!;
      }

      avatarForm.querySelector<HTMLImageElement>("img.userAvatarImage")!.src = link;

      document
        .getElementById("wcf\\action\\UserAvatarAction_avatarFileIDContainer")
        ?.closest("woltlab-core-dialog")
        ?.close();

      showDefaultSuccessSnackbar();
    });
  } else {
    registerCallback("wcf\\action\\UserAvatarAction_avatarFileID", () => {
      document
        .getElementById("wcf\\action\\UserAvatarAction_avatarFileIDContainer")
        ?.closest("woltlab-core-dialog")
        ?.close();

      window.location.reload();
    });
  }

  wheneverFirstSeen("[data-edit-avatar]", (button) => {
    button.addEventListener(
      "click",
      promiseMutex(() => editAvatar(button)),
    );
  });
}
