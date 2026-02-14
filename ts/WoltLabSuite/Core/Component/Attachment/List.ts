import WoltlabCoreFileElement from "../File/woltlab-core-file";
import { CkeditorDropEvent } from "../File/Upload";
import { createAttachmentFromFile, FileProcessorData } from "./Entry";
import { listenToCkeditor } from "../Ckeditor/Event";
import { getTabMenu } from "../Message/MessageTabMenu";
import Sortable from "sortablejs";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { postObject } from "WoltLabSuite/Core/Api/PostObject";
import { debounce } from "WoltLabSuite/Core/Core";
import { getCkeditor } from "../Ckeditor";

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

  const tabMenu = getTabMenu(editorId);

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

  new Sortable(fileList, {
    direction: "vertical",
    dragClass: ".fileList__item",
    ghostClass: "fileList__item--ghost",
    handle: ".fileList__item__file",
    animation: 150,
    fallbackOnBody: true,
    onChange(event) {
      const file = event.item.querySelector("woltlab-core-file")!;
      const thumbnail = file.thumbnails.find((thumbnail) => thumbnail.identifier === "tiny");
      if (thumbnail !== undefined) {
        file.thumbnail = thumbnail;
      } else if (file.link) {
        file.previewUrl = file.link;
      }
    },
    onEnd: promiseMutex(async (event) => {
      if (event.oldIndex === event.newIndex) {
        return;
      }

      const attachmentIDs = Array.from(fileList.querySelectorAll("woltlab-core-file"))
        .map((file) => file.data?.attachmentID)
        .filter((attachmentID) => attachmentID !== undefined);
      const context = JSON.parse(uploadButton.dataset.context!);

      await postObject(`${window.WSC_RPC_API_URL}core/attachments/show-order`, { ...context, attachmentIDs });
    }),
  });

  let showOrder = -1;
  uploadButton.addEventListener("uploadStart", (event: CustomEvent<WoltlabCoreFileElement>) => {
    fileToAttachment(fileList, event.detail, editor);

    const context = JSON.parse(uploadButton.dataset.context!) as Record<string, unknown>;
    context.showOrder = ++showOrder;
    uploadButton.dataset.context = JSON.stringify(context);
  });

  listenToCkeditor(editor)
    .uploadAttachment((payload) => {
      const event = new CustomEvent<CkeditorDropEvent>("ckeditorDrop", {
        detail: payload,
      });
      uploadButton.dispatchEvent(event);

      const messageTabMenu = document.querySelector(`.messageTabMenu[data-wysiwyg-container-id="${editorId}"]`);
      if (messageTabMenu === null) {
        return;
      }

      getTabMenu(editorId)?.setActiveTab("attachments");
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
    })
    .reset(() => {
      fileList.querySelectorAll(".fileList__item").forEach((element) => element.remove());
    });

  const existingFiles = container.querySelector<HTMLElement>(".attachment__list__existingFiles");
  if (existingFiles !== null) {
    existingFiles.querySelectorAll("woltlab-core-file").forEach((file) => {
      fileToAttachment(fileList, file, editor);

      const attachmentShowOrder = file.data?.showOrder;
      if (typeof attachmentShowOrder === "number") {
        showOrder = Math.max(showOrder, attachmentShowOrder);
      }
    });

    existingFiles.remove();
  }

  const files = fileList.getElementsByTagName("woltlab-core-file");
  const observer = new MutationObserver(() => {
    let counter = 0;
    for (const file of files) {
      if (!file.isFailedUpload()) {
        counter++;
      }
    }

    tabMenu?.setTabCounter("attachments", counter);
  });
  observer.observe(fileList, {
    childList: true,
    subtree: true,
  });

  listenToCkeditor(editor)
    .changeData(
      debounce(() => {
        observeInsertedAttachments(editor, files);
      }, 500),
    )
    .ready(() => {
      observeInsertedAttachments(editor, files);
    });
}

function observeInsertedAttachments(element: HTMLElement, files: HTMLCollectionOf<WoltlabCoreFileElement>): void {
  const editor = getCkeditor(element);
  if (editor === undefined) {
    throw new Error(`Could not find the CKEditor for element '${element.id}'.`);
  }

  const embeddedAttachments: Set<number> = new Set();
  editor.element.querySelectorAll(".woltlabAttachment[data-attachment-id]").forEach((img: HTMLImageElement) => {
    const attachmentId = parseInt(img.dataset.attachmentId!);
    embeddedAttachments.add(attachmentId);
  });

  const bbcodeMatches = editor.element.innerText.matchAll(/\[attach=(?:'|")?(\d+)(?:'|")?(?:,[^]]+?)?\]\[\/attach\]/g);
  for (const match of bbcodeMatches) {
    const attachmentId = parseInt(match[1]);
    embeddedAttachments.add(attachmentId);
  }

  for (const file of files) {
    if (file.isFailedUpload()) {
      continue;
    }

    const wrapper = file.closest(".fileList__item");
    if (wrapper === null) {
      continue;
    }

    const { attachmentID } = file.data as FileProcessorData;
    if (embeddedAttachments.has(attachmentID)) {
      wrapper.classList.add("fileList__item--inserted");
    } else {
      wrapper.classList.remove("fileList__item--inserted");
    }
  }
}
