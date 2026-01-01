/**
 * Handles the preview of signatures.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { dboAction } from "WoltLabSuite/Core/Ajax";
import { listenToCkeditor } from "WoltLabSuite/Core/Component/Ckeditor/Event";

type ResponseGetMessagePreview = {
  message: string;
  raw: string;
};

let previewContainer: HTMLElement;

async function loadPreview(message: string): Promise<void> {
  const response = (await dboAction("getMessagePreview", "wcf\\data\\user\\UserProfileAction")
    .payload({
      data: {
        message,
      },
    })
    .dispatch()) as ResponseGetMessagePreview;

  if (previewContainer === undefined) {
    const template = document.getElementById("previewTemplate") as HTMLTemplateElement;
    const fragment = template.content.cloneNode(true);
    document.getElementById("signatureContainer")!.insertAdjacentElement("beforebegin", template);
    template.replaceWith(fragment);

    previewContainer = document.getElementById("previewContainer")!;
  }

  previewContainer.innerHTML = response.message;
}

export function setup(): void {
  listenToCkeditor(document.getElementById("text")!).ready(({ ckeditor }) => {
    document.getElementById("previewButton")?.addEventListener(
      "click",
      promiseMutex(() => loadPreview(ckeditor.getHtml())),
    );
  });
}
