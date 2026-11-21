/**
 * Lists the text modules in a tab below the editor. The list is loaded when
 * the tab is opened for the first time, the insert button of a text module
 * inserts it into the editor.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { renderGridView } from "WoltLabSuite/Core/Api/GridViews/RenderGridView";
import { getTextModule } from "WoltLabSuite/Core/Api/TextModules/GetTextModule";
import { getCkeditor } from "WoltLabSuite/Core/Component/Ckeditor";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import DomUtil from "WoltLabSuite/Core/Dom/Util";

const gridViewClass = "wcf\\system\\gridView\\user\\TextModuleGridView";
const initializedContainers = new WeakSet<HTMLElement>();

class TextModuleList {
  readonly #editor: HTMLElement;
  readonly #gridViewContainer: HTMLElement;
  #loadPromise: Promise<void> | undefined = undefined;

  constructor(editor: HTMLElement, container: HTMLElement) {
    this.#editor = editor;
    this.#gridViewContainer = container.querySelector<HTMLElement>(".textModuleList__gridView")!;

    container.addEventListener("messageTabMenu:activate", () => {
      void this.#load();
    });

    // The tab may have been preselected before this component was set up.
    if (container.classList.contains("active")) {
      void this.#load();
    }

    this.#gridViewContainer.addEventListener("click", (event) => {
      const target = event.target as HTMLElement;

      const insertButton = target.closest<HTMLElement>("[data-text-module-insert]");
      if (insertButton !== null) {
        event.preventDefault();
        void this.#insert(parseInt(insertButton.dataset.textModuleInsert!));

        return;
      }

      if (target.closest(".textModuleList__addButton") !== null) {
        void this.#add();
      }
    });
  }

  #load(): Promise<void> {
    this.#loadPromise ??= renderGridView(gridViewClass, 1, "title", "ASC").then(
      ({ gridView }) => {
        DomUtil.setInnerHtml(this.#gridViewContainer, gridView);
      },
      (error: unknown) => {
        // Allows the next activation of the tab to retry.
        this.#loadPromise = undefined;

        throw error;
      },
    );

    return this.#loadPromise;
  }

  async #insert(textModuleId: number): Promise<void> {
    const ckeditor = getCkeditor(this.#editor);
    if (ckeditor === undefined) {
      return;
    }

    const { message } = await getTextModule(textModuleId);
    ckeditor.insertHtml(message);
  }

  async #add(): Promise<void> {
    const { ok } = await dialogFactory().usingFormBuilder().fromEndpoint(this.#gridViewContainer.dataset.addEndpoint!);
    if (!ok) {
      return;
    }

    showDefaultSuccessSnackbar();

    this.#gridViewContainer
      .querySelector(".gridView__table")
      ?.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
  }
}

export function setup(editorId: string, containerId: string): void {
  const editor = document.getElementById(editorId);
  if (editor === null) {
    throw new Error(`The editor '${editorId}' does not exist.`);
  }

  const container = document.getElementById(containerId);
  if (container === null) {
    throw new Error(`The text module container '${containerId}' does not exist.`);
  }

  if (initializedContainers.has(container)) {
    return;
  }
  initializedContainers.add(container);

  new TextModuleList(editor, container);
}
