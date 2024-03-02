import WoltlabCoreFileElement from "../File/woltlab-core-file";

function upload(fileList: HTMLElement, file: WoltlabCoreFileElement): void {
  const element = document.createElement("li");
  element.classList.add("attachment__list__item");
  element.append(file);
  fileList.append(element);

  void file.ready.then(() => {
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
    upload(fileList!, event.detail);
  });
}
