import WoltlabCoreFileElement from "../File/woltlab-core-file";
import { CkeditorDropEvent } from "../File/Upload";
import { createAttachmentFromFile } from "./Entry";
import { listenToCkeditor } from "../Ckeditor/Event";

// This import has the side effect of registering the `<woltlab-core-file>`
// element. Do not remove!
import "../File/woltlab-core-file";

function fileToAttachment(fileList: HTMLElement, file: WoltlabCoreFileElement, editorId: string): void {
  fileList.append(createAttachmentFromFile(file, editorId));
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
    fileToAttachment(fileList!, event.detail, editorId);
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
      fileToAttachment(fileList!, file, editorId);
    });

    existingFiles.remove();
  }
}
