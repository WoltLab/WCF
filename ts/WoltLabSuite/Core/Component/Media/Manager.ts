/**
 * Shows the media manager in a dialog to insert or select media files.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { getMedia, MediaData } from "WoltLabSuite/Core/Api/Media/GetMedia";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { escapeHTML } from "WoltLabSuite/Core/StringUtil";
import type WoltlabCoreDialogElement from "WoltLabSuite/Core/Element/woltlab-core-dialog";
import { editMedia, setupMediaUpload } from "./Upload";

export type MediaManagerMode = "editor" | "select";

export type MediaManagerOptions = {
  mode: MediaManagerMode;
  imagesOnly?: boolean;
  title?: string;
  /**
   * File that is uploaded right away, the resulting media file is returned
   * once the upload has been completed.
   */
  file?: File;
};

type SelectedMedia = {
  filename: string;
  details: string;
};

const LIST_VIEW_CLASS_NAME = "wcf\\system\\listView\\user\\MediaManagerListView";

async function renderListView(options: MediaManagerOptions): Promise<string> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/list-views/render`);
  url.searchParams.set("listView", LIST_VIEW_CLASS_NAME);
  url.searchParams.set("pageNo", "1");
  url.searchParams.set("sortField", "uploadTime");
  url.searchParams.set("sortOrder", "DESC");
  url.searchParams.set("listViewParameters[mode]", options.mode);
  url.searchParams.set("listViewParameters[imagesOnly]", options.imagesOnly ? "1" : "0");
  url.searchParams.set("listViewParameters[searchString]", "");

  const json = (await prepareRequest(url).get().fetchAsJson()) as { listView: string };

  return json.listView;
}

/**
 * Keeps track of the selected media files, which survives the reloading of
 * the list, and reflects it in the tiles and the footer of the dialog.
 */
class MediaSelection {
  readonly #dialog: WoltlabCoreDialogElement;
  readonly #listViewItems: HTMLElement;
  readonly #multiple: boolean;
  readonly #selected = new Map<number, SelectedMedia>();
  readonly #selectionInfo: HTMLElement;
  #focusedId: number | undefined = undefined;

  constructor(dialog: WoltlabCoreDialogElement, listViewItems: HTMLElement, multiple: boolean) {
    this.#dialog = dialog;
    this.#listViewItems = listViewItems;
    this.#multiple = multiple;

    this.#selectionInfo = document.createElement("div");
    this.#selectionInfo.classList.add("mediaManager__selectionInfo");
    dialog.querySelector("woltlab-core-dialog-control")!.append(this.#selectionInfo);

    listViewItems.setAttribute("role", "listbox");
    if (multiple) {
      listViewItems.setAttribute("aria-multiselectable", "true");
    }

    // The tiles are replaced whenever the list is reloaded.
    new MutationObserver(() => this.render()).observe(listViewItems, { childList: true });

    this.#initEventListeners();
    this.render();
  }

  getSelectedIds(): number[] {
    return Array.from(this.#selected.keys());
  }

  toggle(tile: HTMLElement): void {
    const mediaId = parseInt(tile.dataset.objectId!);
    if (this.#selected.has(mediaId)) {
      this.#selected.delete(mediaId);
      this.#focusedId = mediaId;
      this.render();
    } else {
      this.select(tile);
    }
  }

  select(tile: HTMLElement): void {
    if (!this.#multiple) {
      this.#selected.clear();
    }

    const mediaId = parseInt(tile.dataset.objectId!);
    this.#selected.set(mediaId, {
      filename: tile.dataset.filename!,
      details: tile.dataset.details!,
    });

    this.#focusedId = mediaId;
    this.render();
  }

  getTiles(): HTMLElement[] {
    return Array.from(this.#listViewItems.querySelectorAll<HTMLElement>(".mediaManager__tile"));
  }

  getTile(mediaId: number): HTMLElement | null {
    return this.#listViewItems.querySelector<HTMLElement>(`.mediaManager__tile[data-object-id="${mediaId}"]`);
  }

  render(): void {
    const tiles = this.getTiles();

    let focusableTile = tiles.find((tile) => parseInt(tile.dataset.objectId!) === this.#focusedId);
    focusableTile ??= tiles.find((tile) => this.#selected.has(parseInt(tile.dataset.objectId!)));
    focusableTile ??= tiles[0];

    for (const tile of tiles) {
      const isSelected = this.#selected.has(parseInt(tile.dataset.objectId!));
      tile.classList.toggle("mediaManager__tile--selected", isSelected);
      tile.setAttribute("aria-selected", isSelected ? "true" : "false");
      tile.tabIndex = tile === focusableTile ? 0 : -1;
    }

    this.#renderSelectionInfo();
    this.#dialog.incomplete = this.#selected.size === 0;
  }

  confirm(): void {
    if (this.#selected.size === 0) {
      return;
    }

    this.#dialog.querySelector<HTMLButtonElement>(".dialog__control__button--primary")!.click();
  }

  #renderSelectionInfo(): void {
    this.#selectionInfo.classList.toggle("mediaManager__selectionInfo--empty", this.#selected.size === 0);

    if (this.#selected.size === 0) {
      this.#selectionInfo.textContent = getPhrase("wcf.media.manager.selection.none");
    } else if (this.#selected.size === 1) {
      const { filename, details } = Array.from(this.#selected.values())[0];
      this.#selectionInfo.innerHTML = `<strong>${escapeHTML(filename)}</strong> · ${escapeHTML(details)}`;
    } else {
      this.#selectionInfo.textContent = getPhrase("wcf.media.manager.selection.multiple", {
        count: this.#selected.size,
      });
    }

    this.#selectionInfo.title = this.#selectionInfo.textContent || "";
  }

  #initEventListeners(): void {
    this.#listViewItems.addEventListener("click", (event) => {
      const tile = this.#getTileFromEvent(event);
      if (tile !== null) {
        this.toggle(tile);
      }
    });

    this.#listViewItems.addEventListener("dblclick", (event) => {
      const tile = this.#getTileFromEvent(event);
      if (tile !== null) {
        this.select(tile);
        this.confirm();
      }
    });

    this.#listViewItems.addEventListener("keydown", (event) => {
      const tile = this.#getTileFromEvent(event);
      if (tile === null || event.target !== tile) {
        return;
      }

      switch (event.key) {
        case " ":
          event.preventDefault();
          this.toggle(tile);
          break;

        case "Enter":
          event.preventDefault();
          this.select(tile);
          this.confirm();
          break;

        case "ArrowLeft":
        case "ArrowRight":
        case "ArrowUp":
        case "ArrowDown":
        case "Home":
        case "End":
          event.preventDefault();
          this.#moveFocus(tile, event.key);
          break;
      }
    });
  }

  #getTileFromEvent(event: Event): HTMLElement | null {
    const target = event.target as HTMLElement;

    // The context menu must not change the selection.
    if (target.closest(".mediaManager__tile__menu") !== null) {
      return null;
    }

    return target.closest<HTMLElement>(".mediaManager__tile");
  }

  #moveFocus(tile: HTMLElement, key: string): void {
    const tiles = this.getTiles();
    const index = tiles.indexOf(tile);

    // The number of columns is derived from the tiles in the first row.
    const columns = Math.max(1, tiles.filter((element) => element.offsetTop === tiles[0].offsetTop).length);

    let newIndex = index;
    switch (key) {
      case "ArrowLeft":
        newIndex = index - 1;
        break;
      case "ArrowRight":
        newIndex = index + 1;
        break;
      case "ArrowUp":
        newIndex = index - columns;
        break;
      case "ArrowDown":
        newIndex = index + columns;
        break;
      case "Home":
        newIndex = 0;
        break;
      case "End":
        newIndex = tiles.length - 1;
        break;
    }

    const newTile = tiles[newIndex];
    if (newTile === undefined) {
      return;
    }

    this.#focusedId = parseInt(newTile.dataset.objectId!);
    this.render();
    newTile.focus();
  }
}

/**
 * Resolves with the tiles of the given media files once they have been
 * rendered after a reload of the list.
 */
function waitForTiles(
  selection: MediaSelection,
  listViewItems: HTMLElement,
  mediaIds: number[],
): Promise<HTMLElement[]> {
  const getTiles = () =>
    mediaIds.map((mediaId) => selection.getTile(mediaId)).filter((tile): tile is HTMLElement => tile !== null);

  return new Promise((resolve) => {
    // The list view replaces all items at once, the new media files may not
    // be part of the current page or match the current search though.
    const observer = new MutationObserver(() => {
      observer.disconnect();
      window.clearTimeout(timeout);
      resolve(getTiles());
    });
    observer.observe(listViewItems, { childList: true });

    // An empty list that remains empty does not cause any mutation.
    const timeout = window.setTimeout(() => {
      observer.disconnect();
      resolve(getTiles());
    }, 5_000);
  });
}

function scrollToTile(content: HTMLElement, tile: HTMLElement): void {
  // The toolbar of the list view sticks to the top of the scrolling container.
  const toolbarHeight = content.querySelector<HTMLElement>(".listView__header")?.offsetHeight ?? 0;
  const offset = tile.getBoundingClientRect().top - content.getBoundingClientRect().top;

  content.scrollTop = Math.max(0, content.scrollTop + offset - toolbarHeight - 10);
}

function setupSearch(dialog: WoltlabCoreDialogElement, listViewItems: HTMLElement): void {
  const input = dialog.content.querySelector<HTMLInputElement>(".mediaManager__searchInput");
  if (input === null) {
    return;
  }

  let timeout: number | undefined = undefined;
  input.addEventListener("input", () => {
    window.clearTimeout(timeout);
    timeout = window.setTimeout(() => {
      listViewItems.dispatchEvent(
        new CustomEvent("interaction:set-parameters", {
          detail: { searchString: input.value.trim() },
        }),
      );
    }, 200);
  });
}

function setupDropZone(dialog: WoltlabCoreDialogElement, uploadButton: WoltlabCoreFileUploadElement): void {
  const dropZone = document.createElement("div");
  dropZone.classList.add("mediaManager__dropZone");
  dropZone.innerHTML = `
    <fa-icon name="upload"></fa-icon>
    <span>
      ${getPhrase("wcf.media.manager.dropZone")}
      <button type="button" class="mediaManager__dropZone__select">${getPhrase("wcf.media.manager.dropZone.select")}</button>
    </span>
  `;
  dropZone.querySelector("button")!.addEventListener("click", () => {
    uploadButton.click();
  });
  dialog.content.querySelector(".listView__itemContainer")!.before(dropZone);

  // The whole content area accepts the files while dragging them over the dialog.
  const content = dialog.content;
  const hasFiles = (event: DragEvent) => event.dataTransfer?.types.includes("Files") === true;
  let dragCounter = 0;

  content.addEventListener("dragenter", (event) => {
    if (!hasFiles(event)) {
      return;
    }

    event.preventDefault();
    dragCounter++;
    content.classList.add("mediaManager--dragOver");
  });
  content.addEventListener("dragover", (event) => {
    if (hasFiles(event)) {
      event.preventDefault();
    }
  });
  content.addEventListener("dragleave", () => {
    dragCounter = Math.max(0, dragCounter - 1);
    if (dragCounter === 0) {
      content.classList.remove("mediaManager--dragOver");
    }
  });
  content.addEventListener("drop", (event) => {
    if (!hasFiles(event)) {
      return;
    }

    event.preventDefault();
    dragCounter = 0;
    content.classList.remove("mediaManager--dragOver");

    const files = Array.from(event.dataTransfer!.files);
    if (files.length > 0) {
      uploadButton.uploadFiles(files);
    }
  });
}

/**
 * Opens the media manager and resolves with the media files that the user has
 * inserted or selected, an empty list is returned if the dialog was closed.
 */
export async function openMediaManager(options: MediaManagerOptions): Promise<MediaData[]> {
  const multiple = options.mode === "editor";

  const dialog = dialogFactory()
    .fromHtml(await renderListView(options))
    .asPrompt({
      primary: getPhrase(multiple ? "wcf.global.button.insert" : "wcf.media.button.select"),
    });
  dialog.dialog.classList.add("dialog--mediaManager");
  dialog.show(options.title || getPhrase(multiple ? "wcf.media.manager" : "wcf.media.chooseFile"));

  const listViewItems = dialog.content.querySelector<HTMLElement>(".listView__items")!;
  const uploadButton = dialog.content.querySelector<WoltlabCoreFileUploadElement>("woltlab-core-file-upload");

  const selection = new MediaSelection(dialog, listViewItems, multiple);
  setupSearch(dialog, listViewItems);

  return new Promise<MediaData[]>((resolve) => {
    let completed = false;

    const complete = async (mediaIds: number[]): Promise<void> => {
      if (completed) {
        return;
      }
      completed = true;

      const media = await Promise.all(mediaIds.map((mediaId) => getMedia(mediaId)));

      if (dialog.isConnected) {
        dialog.close();
      }
      resolve(media);
    };

    dialog.addEventListener("primary", () => {
      void complete(selection.getSelectedIds());
    });

    if (uploadButton !== null) {
      setupDropZone(dialog, uploadButton);

      // The upload element is hidden and triggered by the primary button of the list view.
      const proxyButton = dialog.content.querySelector<HTMLButtonElement>(".mediaManager__uploadButton");
      if (proxyButton !== null) {
        proxyButton.addEventListener("click", () => {
          uploadButton.click();
        });
        uploadButton.addEventListener("uploadStart", () => {
          proxyButton.disabled = true;
        });
      }

      setupMediaUpload(uploadButton, {
        uploadCompleted: (mediaId) => {
          if (options.file !== undefined) {
            void complete([mediaId]);
          }
        },
        allUploadsCompleted: async (mediaIds) => {
          if (proxyButton !== null) {
            proxyButton.disabled = false;
          }

          if (!listViewItems.isConnected || options.file !== undefined || mediaIds.length === 0) {
            return;
          }

          // Reload the list only once after a batch of files has been uploaded.
          const tilesPromise = waitForTiles(selection, listViewItems, mediaIds);
          listViewItems.dispatchEvent(new CustomEvent("interaction:invalidate-all"));

          // Select the uploaded media files, a single selection selects the last one.
          const tiles = await tilesPromise;
          for (const tile of multiple ? tiles : tiles.slice(-1)) {
            selection.select(tile);
          }
          if (tiles.length > 0) {
            scrollToTile(dialog.content, tiles[0]);
          }

          // A single uploaded file is edited right away.
          const editEndpoint = uploadButton.closest<HTMLElement>(".mediaManager__upload")?.dataset.editEndpoint;
          if (mediaIds.length !== 1 || editEndpoint === undefined) {
            return;
          }

          if ((await editMedia(editEndpoint, mediaIds[0])) && listViewItems.isConnected) {
            listViewItems.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
          }
        },
      });

      const file = options.file;
      if (file !== undefined) {
        // The upload button is initialized by a mutation observer.
        window.setTimeout(() => {
          uploadButton.uploadFiles([file]);
        }, 0);
      }
    }

    dialog.addEventListener("afterClose", () => {
      if (!completed) {
        completed = true;
        resolve([]);
      }
    });
  });
}
