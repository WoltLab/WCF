import WoltlabCoreFileElement from "../File/woltlab-core-file";

function upload(fileList: HTMLElement, file: WoltlabCoreFileElement): void {
  // TODO: Any sort of upload indicator, meter, spinner, whatever?
  const element = document.createElement("li");
  element.classList.add("attachment__list__item");
  element.append(file);
  fileList.append(element);

  void file.ready.then(() => {
    // TODO: Do something?
  });
}

export function setup(container: HTMLElement): void {
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
    // TODO: How do we keep track of the files being uploaded?
    upload(fileList!, event.detail);
  });
}
