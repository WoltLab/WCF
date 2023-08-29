/**
 * Forwards upload requests from the editor to the attachment system.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @woltlabExcludeBundle tiny
 */

import { dispatchToCkeditor, listenToCkeditor } from "./Event";

import type { CKEditor } from "../Ckeditor";

type UploadResult = {
  [key: string]: unknown;
  urls: {
    default: string;
  };
};

type AttachmentData = {
  attachmentId: number;
  url: string;
};

export type UploadAttachmentEventPayload = {
  abortController?: AbortController;
  file: File;
  promise?: Promise<AttachmentData>;
};

function uploadAttachment(element: HTMLElement, file: File, abortController?: AbortController): Promise<UploadResult> {
  const payload: UploadAttachmentEventPayload = { abortController, file };

  dispatchToCkeditor(element).uploadAttachment(payload);

  return new Promise<UploadResult>((resolve) => {
    void payload.promise!.then(({ attachmentId, url }) => {
      resolve({
        "data-attachment-id": attachmentId.toString(),
        urls: {
          default: url,
        },
      });
    });
  });
}

export type InsertAttachmentPayload = {
  attachmentId: number;
  url: string;
};

function setupInsertAttachment(ckeditor: CKEditor): void {
  listenToCkeditor(ckeditor.sourceElement).insertAttachment(({ attachmentId, url }) => {
    if (url === "") {
      ckeditor.insertText(`[attach=${attachmentId}][/attach]`);
    } else {
      ckeditor.insertHtml(
        `<img src="${url}" class="woltlabAttachment" data-attachment-id="${attachmentId.toString()}">`,
      );
    }
  });
}

export type RemoveAttachmentPayload = {
  attachmentId: number;
};

function setupRemoveAttachment(ckeditor: CKEditor): void {
  listenToCkeditor(ckeditor.sourceElement).removeAttachment(({ attachmentId }) => {
    ckeditor.removeAll("imageBlock", { attachmentId });
    ckeditor.removeAll("imageInline", { attachmentId });
  });
}

function getInlineImageIds(element: HTMLElement): number[] {
  const messageTabMenu = document.querySelector<HTMLElement>(
    `.messageTabMenu[data-wysiwyg-container-id="${element.id}"]`,
  );
  if (!messageTabMenu) {
    return [];
  }

  const attachmentList = messageTabMenu.querySelector<HTMLElement>(".formAttachmentContent > .formAttachmentList");
  if (!attachmentList) {
    return [];
  }

  return Array.from(attachmentList.querySelectorAll<HTMLElement>('.formAttachmentListItem[data-is-image="1"]')).map(
    (listItem) => parseInt(listItem.dataset.objectId!),
  );
}

export function setup(element: HTMLElement): void {
  listenToCkeditor(element).setupConfiguration(({ configuration, features }) => {
    if (!features.attachment) {
      return;
    }

    const inlineImageIds = getInlineImageIds(element);

    // TODO: The typings do not include our custom plugins yet.
    (configuration as any).woltlabAttachment = {
      inlineImageIds,
      resolveAttachmentUrl(attachmentId: number, isThumbnail: boolean) {
        let thumbnail = "";
        if (isThumbnail) {
          thumbnail = "&thumbnail=1";
        }

        return `${window.WSC_API_URL}index.php?attachment/${attachmentId}/${thumbnail}`;
      },
    };
    (configuration as any).woltlabUpload = {
      uploadImage: (file: File, abortController: AbortController) => uploadAttachment(element, file, abortController),
      uploadOther: (file: File) => uploadAttachment(element, file),
    };

    listenToCkeditor(element).ready(({ ckeditor }) => {
      setupInsertAttachment(ckeditor);
      setupRemoveAttachment(ckeditor);
    });
  });
}
