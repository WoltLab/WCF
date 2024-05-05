import { formatFilesize } from "WoltLabSuite/Core/FileUtil";
import type WoltlabCoreFileElement from "../File/woltlab-core-file";
import { initFragment, toggleDropdown } from "WoltLabSuite/Core/Ui/Dropdown/Simple";
import DomChangeListener from "WoltLabSuite/Core/Dom/Change/Listener";
import { dispatchToCkeditor } from "../Ckeditor/Event";
import { deleteFile } from "WoltLabSuite/Core/Api/Files/DeleteFile";

type FileProcessorData = {
  attachmentID: number;
};

function fileInitializationCompleted(element: HTMLElement, file: WoltlabCoreFileElement, editorId: string): void {
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

  const extraButtons: HTMLButtonElement[] = [];

  let insertButton: HTMLButtonElement;
  if (file.isImage()) {
    const thumbnail = file.thumbnails.find((thumbnail) => thumbnail.identifier === "tiny");
    if (thumbnail !== undefined) {
      file.thumbnail = thumbnail;
    }

    const url = file.thumbnails.find((thumbnail) => thumbnail.identifier === "")?.link;
    if (url !== undefined) {
      insertButton = getInsertThumbnailButton((data as FileProcessorData).attachmentID, url, editorId);

      extraButtons.push(
        getInsertAttachBbcodeButton((data as FileProcessorData).attachmentID, file.link ? file.link : "", editorId),
      );
    } else {
      insertButton = getInsertAttachBbcodeButton(
        (data as FileProcessorData).attachmentID,
        file.link ? file.link : "",
        editorId,
      );
    }

    if (file.link !== undefined && file.filename !== undefined) {
      const link = document.createElement("a");
      link.href = file.link!;
      link.classList.add("jsImageViewer");
      link.title = file.filename;
      link.textContent = file.filename;

      const filename = element.querySelector(".attachment__item__filename")!;
      filename.innerHTML = "";
      filename.append(link);

      DomChangeListener.trigger();
    }
  } else {
    insertButton = getInsertAttachBbcodeButton(
      (data as FileProcessorData).attachmentID,
      file.isImage() && file.link ? file.link : "",
      editorId,
    );
  }

  const dropdownMenu = document.createElement("ul");
  dropdownMenu.classList.add("dropdownMenu");
  for (const button of extraButtons) {
    const listItem = document.createElement("li");
    listItem.append(button);
    dropdownMenu.append(listItem);
  }

  if (dropdownMenu.childElementCount !== 0) {
    const listItem = document.createElement("li");
    listItem.classList.add("dropdownDivider");
    dropdownMenu.append(listItem);
  }

  const listItem = document.createElement("li");
  listItem.append(getDeleteAttachButton(fileId, (data as FileProcessorData).attachmentID, editorId, element));
  dropdownMenu.append(listItem);

  const moreOptions = document.createElement("button");
  moreOptions.classList.add("button", "small", "jsTooltip");
  moreOptions.type = "button";
  moreOptions.title = "TODO: more options";
  moreOptions.innerHTML = '<fa-icon name="ellipsis-vertical"></fa-icon>';

  const buttonList = document.createElement("div");
  buttonList.classList.add("attachment__item__buttons");
  insertButton.classList.add("button", "small");
  buttonList.append(insertButton, moreOptions);

  element.append(buttonList);

  initFragment(moreOptions, dropdownMenu);
  moreOptions.addEventListener("click", (event) => {
    event.stopPropagation();

    toggleDropdown(moreOptions.id);
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

function fileInitializationFailed(element: HTMLElement, file: WoltlabCoreFileElement, reason: unknown): void {
  if (reason instanceof Error) {
    throw reason;
  }

  if (file.validationError === undefined) {
    return;
  }

  // TODO: Add a proper error message, this is for development purposes only.
  element.append(JSON.stringify(file.validationError));
  element.classList.add("attachment__item--error");
}

export function createAttachmentFromFile(file: WoltlabCoreFileElement, editorId: string) {
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

  void file.ready
    .then(() => {
      fileInitializationCompleted(element, file, editorId);
    })
    .catch((reason) => {
      fileInitializationFailed(element, file, reason);
    });

  return element;
}
