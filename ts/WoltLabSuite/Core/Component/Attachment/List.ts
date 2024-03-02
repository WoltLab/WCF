import { dispatchToCkeditor } from "../Ckeditor/Event";
import WoltlabCoreFileElement from "../File/woltlab-core-file";

function upload(fileList: HTMLElement, file: WoltlabCoreFileElement, editorId: string): void {
  const element = document.createElement("li");
  element.classList.add("attachment__list__item");
  element.append(file);
  fileList.append(element);

  void file.ready.then(() => {
    element.append(getInsertAttachBbcodeButton(file, editorId));

    if (file.isImage()) {
      const thumbnail = file.thumbnails.find((thumbnail) => {
        return thumbnail.identifier === "tiny";
      });

      if (thumbnail !== undefined) {
        file.thumbnail = thumbnail;
      }
    }
  });
}

function getInsertAttachBbcodeButton(file: WoltlabCoreFileElement, editorId: string): HTMLButtonElement {
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
      attachmentId: 123, // TODO: how do we get the id?
      url: "",
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
    // TODO: We need to forward the attachment data from the event once it
    //       becoems available.
    upload(fileList!, event.detail, editorId);
  });
}
