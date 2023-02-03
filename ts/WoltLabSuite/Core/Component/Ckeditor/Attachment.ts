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
  element: HTMLElement,
  file: File,
  abortController?: AbortController,
): Promise<UploadResult> {
  const data: DragAndDropEventData = { abortController, file };

  element.dispatchEvent(
    new CustomEvent<DragAndDropEventData>("ckeditor5:drop", {
      detail: data,
    }),
  );

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

type InsertAttachmentPayload = {
  attachmentId: number;
  url: string;
};

export function setupInsertAttachment(ckeditor: CKEditor): void {
  ckeditor.sourceElement.addEventListener(
    "ckeditor5:insert-attachment",
    (event: CustomEvent<InsertAttachmentPayload>) => {
      const { attachmentId, url } = event.detail;

      if (url === "") {
        ckeditor.insertText(`[attach=${attachmentId}][/attach]`);
      } else {
        ckeditor.insertHtml(
          `<img src="${url}" class="woltlabAttachment" data-attachment-id="${attachmentId.toString()}">`,
        );
      }
    },
  );
}

export function setupRemoveAttachment(ckeditor: CKEditor): void {
  ckeditor.sourceElement.addEventListener(
    "ckeditor5:remove-attachment",
    ({ detail: attachmentId }: CustomEvent<number>) => {
      ckeditor.removeAll("imageBlock", { attachmentId });
      ckeditor.removeAll("imageInline", { attachmentId });
    },
  );
}
