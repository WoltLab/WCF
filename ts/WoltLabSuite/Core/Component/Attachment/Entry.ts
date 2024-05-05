import { formatFilesize } from "WoltLabSuite/Core/FileUtil";
import type WoltlabCoreFileElement from "../File/woltlab-core-file";
import { initFragment, toggleDropdown } from "WoltLabSuite/Core/Ui/Dropdown/Simple";
import DomChangeListener from "WoltLabSuite/Core/Dom/Change/Listener";
import { dispatchToCkeditor } from "../Ckeditor/Event";
import { deleteFile } from "WoltLabSuite/Core/Api/Files/DeleteFile";
import { getPhrase } from "WoltLabSuite/Core/Language";

type FileProcessorData = {
  attachmentID: number;
};

function fileInitializationCompleted(element: HTMLElement, file: WoltlabCoreFileElement, editor: HTMLElement): void {
  const data = file.data;
  if (data === undefined) {
    throw new Error("No meta data was returned from the server.", {
      cause: {
        file,
      },
    });
  }

  const fileId = file.fileId;
  if (fileId === undefined) {
    throw new Error("The file id is not set.", {
      cause: {
        file,
      },
    });
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
      insertButton = getInsertButton((data as FileProcessorData).attachmentID, url, editor);

      const insertOriginalImage = getInsertButton((data as FileProcessorData).attachmentID, file.link!, editor);
      insertOriginalImage.textContent = getPhrase("wcf.attachment.insertFull");
      extraButtons.push(insertOriginalImage);
    } else {
      insertButton = getInsertButton((data as FileProcessorData).attachmentID, file.link ? file.link : "", editor);
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
    insertButton = getInsertButton(
      (data as FileProcessorData).attachmentID,
      file.isImage() && file.link ? file.link : "",
      editor,
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
  listItem.append(getDeleteAttachButton(fileId, (data as FileProcessorData).attachmentID, editor, element));
  dropdownMenu.append(listItem);

  const moreOptions = document.createElement("button");
  moreOptions.classList.add("button", "small");
  moreOptions.type = "button";
  moreOptions.setAttribute("aria-label", getPhrase("wcf.global.button.more"));
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
  editor: HTMLElement,
  element: HTMLElement,
): HTMLButtonElement {
  const button = document.createElement("button");
  button.type = "button";
  button.textContent = getPhrase("wcf.global.button.delete");

  button.addEventListener("click", () => {
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

function getInsertButton(attachmentId: number, url: string, editor: HTMLElement): HTMLButtonElement {
  const button = document.createElement("button");
  button.type = "button";
  button.textContent = getPhrase("wcf.attachment.insert");

  button.addEventListener("click", () => {
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

  let errorMessage: string;
  switch (file.validationError.param) {
    case "preflight":
      errorMessage = getPhrase(`wcf.upload.error.${file.validationError.code}`);
      break;

    default:
      errorMessage = "Unrecognized error type: " + JSON.stringify(file.validationError);
      break;
  }

  markElementAsErroneous(element, errorMessage);
}

function markElementAsErroneous(element: HTMLElement, errorMessage: string): void {
  element.classList.add("attachment__item--error");

  const errorElement = document.createElement("div");
  errorElement.classList.add("attachemnt__item__errorMessage");
  errorElement.textContent = errorMessage;

  element.append(errorElement);
}

export function createAttachmentFromFile(file: WoltlabCoreFileElement, editor: HTMLElement) {
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
      fileInitializationCompleted(element, file, editor);
    })
    .catch((reason) => {
      fileInitializationFailed(element, file, reason);
    });

  return element;
}
