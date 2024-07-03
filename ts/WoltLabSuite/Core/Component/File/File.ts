import WoltlabCoreFileElement from "WoltLabSuite/Core/Component/File/woltlab-core-file";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { formatFilesize } from "WoltLabSuite/Core/FileUtil";

export function trackUploadProgress(element: HTMLElement, file: WoltlabCoreFileElement): void {
  const progress = document.createElement("progress");
  progress.classList.add("fileList__item__progress__bar");
  progress.max = 100;
  const readout = document.createElement("span");
  readout.classList.add("fileList__item__progress__readout");

  file.addEventListener("uploadProgress", (event: CustomEvent<number>) => {
    progress.value = event.detail;
    readout.textContent = `${event.detail}%`;

    if (progress.parentNode === null) {
      element.classList.add("fileProcessor__item--uploading");

      const wrapper = document.createElement("div");
      wrapper.classList.add("fileList__item__progress");
      wrapper.append(progress, readout);

      element.append(wrapper);
    }
  });
}

export function removeUploadProgress(element: HTMLElement): void {
  if (!element.classList.contains("fileProcessor__item--uploading")) {
    return;
  }

  element.classList.remove("fileProcessor__item--uploading");
  element.querySelector(".fileList__item__progress")?.remove();
}

export function fileInitializationFailed(element: HTMLElement, file: WoltlabCoreFileElement, reason: unknown): void {
  if (reason instanceof Error) {
    throw reason;
  }

  if (file.apiError === undefined) {
    return;
  }

  let errorMessage: string;

  const validationError = file.apiError.getValidationError();
  if (validationError !== undefined) {
    switch (validationError.param) {
      case "preflight":
        errorMessage = getPhrase(`wcf.upload.error.${validationError.code}`);
        break;

      default:
        errorMessage = "Unrecognized error type: " + JSON.stringify(validationError);
        break;
    }
  } else {
    errorMessage = `Unexpected server error: [${file.apiError.type}] ${file.apiError.message}`;
  }

  markElementAsErroneous(element, errorMessage);
}

function markElementAsErroneous(element: HTMLElement, errorMessage: string): void {
  element.classList.add("fileList__item--error");

  const errorElement = document.createElement("div");
  errorElement.classList.add("fileList__item__errorMessage");
  errorElement.textContent = errorMessage;

  element.append(errorElement);
}

export function insertFileInformation(container: HTMLElement, file: WoltlabCoreFileElement): void {
  const fileWrapper = document.createElement("div");
  fileWrapper.classList.add("fileList__item__file");
  fileWrapper.append(file);

  const filename = document.createElement("div");
  filename.classList.add("fileList__item__filename");
  filename.textContent = file.filename || file.dataset.filename!;

  const fileSize = document.createElement("div");
  fileSize.classList.add("fileList__item__fileSize");
  fileSize.textContent = formatFilesize(file.fileSize || parseInt(file.dataset.fileSize!));

  container.append(fileWrapper, filename, fileSize);
}
