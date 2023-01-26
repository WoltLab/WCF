import * as EventHandler from "../../Event/Handler";
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

type DragAndDropEventData = {
  abortController?: AbortController;
  file: File;
  promise?: Promise<AttachmentData>;
};

export function uploadAttachment(
  elementId: string,
  file: File,
  abortController?: AbortController,
): Promise<UploadResult> {
  const data: DragAndDropEventData = { abortController, file };
  EventHandler.fire("com.woltlab.wcf.ckeditor5", `dragAndDrop_${elementId}`, data);

  return new Promise<UploadResult>((resolve) => {
    void data.promise!.then(({ attachmentId, url }) => {
      resolve({
        "data-attachment-id": attachmentId.toString(),
        urls: {
          default: url,
        },
      });
    });
  });
}

export function setupRemoveAttachment(ckeditor: CKEditor): void {
  EventHandler.add(
    "com.woltlab.wcf.ckeditor5",
    `removeEmbeddedAttachment_${ckeditor.sourceElement.id}`,
    ({ attachmentId }) => {
      ckeditor.removeAll("imageBlock", { attachmentId });
      ckeditor.removeAll("imageInline", { attachmentId });
    },
  );
}
