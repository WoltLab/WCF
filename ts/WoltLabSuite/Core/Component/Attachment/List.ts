import { deleteFile } from "WoltLabSuite/Core/Api/Files/DeleteFile";
import { dispatchToCkeditor, listenToCkeditor } from "../Ckeditor/Event";
import WoltlabCoreFileElement from "../File/woltlab-core-file";

import "../File/woltlab-core-file";
import { CkeditorDropEvent } from "../File/Upload";
import { formatFilesize } from "WoltLabSuite/Core/FileUtil";

type FileProcessorData = {
  attachmentID: number;
};

function upload(fileList: HTMLElement, file: WoltlabCoreFileElement, editorId: string): void {
  const element = document.createElement("li");
  element.classList.add("attachment__item");

  const fileWrapper = document.createElement("div");
  fileWrapper.classList.add("attachment__item__file");
  fileWrapper.append(file);

  const filename = document.createElement("div");
  filename.classList.add("attachment__item__filename");
  filename.textContent = file.filename || file.dataset.filename!;

  const fileSize = document.createElement("div");
  fileSize.classList.add("attachment__item__fileSize");
  fileSize.textContent = formatFilesize(file.fileSize || parseInt(file.dataset.fileSize!));

  element.append(fileWrapper, filename, fileSize);
  fileList.append(element);

  void file.ready
    .then(() => {
      const data = file.data;
      if (data === undefined) {
        // TODO: error handling
        return;
      }

      const fileId = file.fileId;
      if (fileId === undefined) {
        // TODO: error handling
        return;
      }

      const buttonList = document.createElement("div");
      buttonList.classList.add("attachment__item__buttons");
      buttonList.append(
        getDeleteAttachButton(fileId, (data as FileProcessorData).attachmentID, editorId, element),
        getInsertAttachBbcodeButton(
          (data as FileProcessorData).attachmentID,
          file.isImage() && file.link ? file.link : "",
          editorId,
        ),
      );

      if (file.isImage()) {
        const thumbnail = file.thumbnails.find((thumbnail) => thumbnail.identifier === "tiny");
        if (thumbnail !== undefined) {
          file.thumbnail = thumbnail;
        }

        const url = file.thumbnails.find((thumbnail) => thumbnail.identifier === "")?.link;
        if (url !== undefined) {
          buttonList.append(getInsertThumbnailButton((data as FileProcessorData).attachmentID, url, editorId));
        }
      }

      element.append(buttonList);
    })
    .catch(() => {
      if (file.validationError === undefined) {
        return;
      }

      // TODO: Add a proper error message, this is for development purposes only.
      element.append(JSON.stringify(file.validationError));
      element.classList.add("attachment__item--error");
    });
}

function getDeleteAttachButton(
  fileId: number,
  attachmentId: number,
  editorId: string,
  element: HTMLElement,
): HTMLButtonElement {
  const button = document.createElement("button");
  button.type = "button";
  button.classList.add("button", "small");
  button.textContent = "TODO: delete";

  button.addEventListener("click", () => {
    const editor = document.getElementById(editorId);
    if (editor === null) {
      // TODO: error handling
      return;
    }

    void deleteFile(fileId).then((result) => {
      result.unwrap();

      dispatchToCkeditor(editor).removeAttachment({
        attachmentId,
      });

      element.remove();
    });
  });

  return button;
}

function getInsertAttachBbcodeButton(attachmentId: number, url: string, editorId: string): HTMLButtonElement {
  const button = document.createElement("button");
  button.type = "button";
  button.classList.add("button", "small");
  button.textContent = "TODO: insert";

  button.addEventListener("click", () => {
    const editor = document.getElementById(editorId);
    if (editor === null) {
      // TODO: error handling
      return;
    }

    dispatchToCkeditor(editor).insertAttachment({
      attachmentId,
      url,
    });
  });

  return button;
}

function getInsertThumbnailButton(attachmentId: number, url: string, editorId: string): HTMLButtonElement {
  const button = document.createElement("button");
  button.type = "button";
  button.classList.add("button", "small");
  button.textContent = "TODO: insert thumbnail";

  button.addEventListener("click", () => {
    const editor = document.getElementById(editorId);
    if (editor === null) {
      // TODO: error handling
      return;
    }

    dispatchToCkeditor(editor).insertAttachment({
      attachmentId,
      url,
    });
  });

  return button;
}

export function setup(editorId: string): void {
  const container = document.getElementById(`attachments_${editorId}`);
  if (container === null) {
    // TODO: error handling
    return;
  }

  const editor = document.getElementById(editorId);
  if (editor === null) {
    // TODO: error handling
    return;
  }

  const uploadButton = container.querySelector("woltlab-core-file-upload");
  if (uploadButton === null) {
    throw new Error("Expected the container to contain an upload button", {
      cause: {
        container,
      },
    });
  }

  let fileList = container.querySelector<HTMLElement>(".attachment__list");
  if (fileList === null) {
    fileList = document.createElement("ol");
    fileList.classList.add("attachment__list");
    uploadButton.insertAdjacentElement("afterend", fileList);
  }

  uploadButton.addEventListener("uploadStart", (event: CustomEvent<WoltlabCoreFileElement>) => {
    upload(fileList!, event.detail, editorId);
  });

  listenToCkeditor(editor).uploadAttachment((payload) => {
    const event = new CustomEvent<CkeditorDropEvent>("ckeditorDrop", {
      detail: payload,
    });
    uploadButton.dispatchEvent(event);
  });

  const existingFiles = container.querySelector<HTMLElement>(".attachment__list__existingFiles");
  if (existingFiles !== null) {
    existingFiles.querySelectorAll("woltlab-core-file").forEach((file) => {
      upload(fileList!, file, editorId);
    });

    existingFiles.remove();
  }
}
