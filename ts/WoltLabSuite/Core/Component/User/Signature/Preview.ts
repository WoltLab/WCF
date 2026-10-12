/**
 * Handles the preview of signatures.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { renderSignaturePreview } from "WoltLabSuite/Core/Api/Users/RenderSignaturePreview";
import { listenToCkeditor } from "WoltLabSuite/Core/Component/Ckeditor/Event";
import type { CKEditor } from "WoltLabSuite/Core/Component/Ckeditor";
import { innerError } from "WoltLabSuite/Core/Dom/Util";

let previewContainer: HTMLElement;

async function loadPreview(ckeditor: CKEditor): Promise<void> {
  const response = await renderSignaturePreview(ckeditor.getHtml());
  if (!response.ok) {
    const validationError = response.error.getValidationError();
    if (validationError === undefined) {
      throw new Error("Unexpected validation error", { cause: response.error });
    }

    innerError(ckeditor.element, validationError.code);
    return;
  }

  innerError(ckeditor.element, false);

  if (previewContainer === undefined) {
    const template = document.getElementById("previewTemplate") as HTMLTemplateElement;
    const fragment = template.content.cloneNode(true);
    document.getElementById("signatureContainer")!.insertAdjacentElement("beforebegin", template);
    template.replaceWith(fragment);

    previewContainer = document.getElementById("previewContainer")!;
  }

  previewContainer.innerHTML = response.value.message;
}

export function setup(): void {
  listenToCkeditor(document.getElementById("text")!).ready(({ ckeditor }) => {
    document.getElementById("previewButton")?.addEventListener(
      "click",
      promiseMutex(() => loadPreview(ckeditor)),
    );
  });
}
