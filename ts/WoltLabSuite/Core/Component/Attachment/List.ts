import WoltlabCoreFileElement from "../File/woltlab-core-file";
import { CkeditorDropEvent } from "../File/Upload";
import { createAttachmentFromFile } from "./Entry";
import { listenToCkeditor } from "../Ckeditor/Event";

function fileToAttachment(fileList: HTMLElement, file: WoltlabCoreFileElement, editor: HTMLElement): void {
  fileList.append(createAttachmentFromFile(file, editor));
}

type Context = {
  tmpHash: string;
};

export function setup(editorId: string): void {
  const container = document.getElementById(`attachments_${editorId}`);
  if (container === null) {
    throw new Error(`The attachments container for '${editorId}' does not exist.`);
  }

  const editor = document.getElementById(editorId);
  if (editor === null) {
    throw new Error(`The editor element for '${editorId}' does not exist.`);
  }

  const uploadButton = container.querySelector("woltlab-core-file-upload");
  if (uploadButton === null) {
    throw new Error("Expected the container to contain an upload button", {
      cause: {
        container,
      },
    });
  }

  let fileList = container.querySelector<HTMLElement>(".fileList");
  if (fileList === null) {
    fileList = document.createElement("ol");
    fileList.classList.add("fileList");
    uploadButton.insertAdjacentElement("afterend", fileList);
  }

  uploadButton.addEventListener("uploadStart", (event: CustomEvent<WoltlabCoreFileElement>) => {
    fileToAttachment(fileList!, event.detail, editor);
  });

  listenToCkeditor(editor)
    .uploadAttachment((payload) => {
      const event = new CustomEvent<CkeditorDropEvent>("ckeditorDrop", {
        detail: payload,
      });
      uploadButton.dispatchEvent(event);
    })
    .collectMetaData((payload) => {
      let context: Context | undefined = undefined;
      try {
        if (uploadButton.dataset.context !== undefined) {
          context = JSON.parse(uploadButton.dataset.context);
        }
      } catch (e) {
        if (window.ENABLE_DEBUG_MODE) {
          console.warn("Unable to parse the context.", e);
        }
      }

      if (context !== undefined) {
        payload.metaData.tmpHash = context.tmpHash;
      }
    });

  const existingFiles = container.querySelector<HTMLElement>(".attachment__list__existingFiles");
  if (existingFiles !== null) {
    existingFiles.querySelectorAll("woltlab-core-file").forEach((file) => {
      fileToAttachment(fileList!, file, editor);
    });

    existingFiles.remove();
  }
}
