{
  class WoltlabCoreFileUploadElement extends HTMLElement {
    readonly #element: HTMLInputElement;
    readonly #icon: FaIcon;
    readonly #text: HTMLSpanElement;
    #pendingProcesses = 0;

    constructor() {
      super();

      this.#element = document.createElement("input");
      this.#element.type = "file";
      this.#element.classList.add("woltlabCoreFileUpload__input");

      // Prevents the underlying dialog from being closed when the File selection dialog is closed by the user.
      this.#element.addEventListener("cancel", (event) => {
        event.stopPropagation();
      });

      this.#element.addEventListener("change", () => {
        const { files } = this.#element;
        if (files === null || files.length === 0) {
          return;
        }

        for (const file of files) {
          /** @deprecated 6.1 No longer supported */
          const event = new CustomEvent<File>("shouldUpload", {
            cancelable: true,
            detail: file,
          });
          this.dispatchEvent(event);

          if (event.defaultPrevented) {
            continue;
          }

          /** @deprecated 6.1 Use `upload:files` instead */
          const uploadEvent = new CustomEvent<File>("upload", {
            detail: file,
          });
          this.dispatchEvent(uploadEvent);
        }

        this.uploadFiles(Array.from(files));

        // Reset the selected file.
        this.#element.value = "";
      });

      // Pass-through for the synthethic click event.
      this.addEventListener("click", (event) => {
        if (event.target !== this) {
          return;
        }

        this.#element.click();
      });

      this.#icon = document.createElement("fa-icon");
      this.#text = document.createElement("span");
    }

    connectedCallback() {
      const allowedFileExtensions = this.dataset.fileExtensions || "";
      if (allowedFileExtensions !== "") {
        this.#element.accept = allowedFileExtensions;
      }

      const maximumCount = this.maximumCount;
      if (maximumCount > 1 || maximumCount === -1) {
        this.#element.multiple = true;
      }

      const button = document.createElement("button");
      button.type = "button";
      button.classList.add("button", "woltlabCoreFileUpload__button");
      button.addEventListener("keydown", (event) => {
        // The `click` event cannot be used here because it would trigger twice
        // when the input element is at the same position. Instead we handle the
        // keyboard event in case the user focuses the element manually.
        if (event.key === "Enter" || event.key === " ") {
          this.#element.click();
        }
      });
      button.append(this.#icon, this.#text, this.#element);

      this.#updateButton();

      this.append(button);
    }

    uploadFiles(files: File[]): void {
      const event = new CustomEvent<{ files: File[] }>("upload:files", {
        detail: {
          files,
        },
      });
      this.dispatchEvent(event);
    }

    markAsBusy(): void {
      this.#pendingProcesses++;

      this.#updateButton();
    }

    markAsReady(): void {
      this.#pendingProcesses--;

      this.#updateButton();
    }

    get maximumCount(): number {
      return parseInt(this.dataset.maximumCount || "1");
    }

    get maximumSize(): number {
      return parseInt(this.dataset.maximumSize || "-1");
    }

    get disabled(): boolean {
      return this.#element.disabled;
    }

    set disabled(disabled: boolean) {
      this.#element.disabled = Boolean(disabled);
    }

    #updateButton(): void {
      const iconName = this.#pendingProcesses === 0 ? "upload" : "spinner";
      const phrase = this.#pendingProcesses === 0 ? "wcf.global.button.upload" : "wcf.global.loading";

      this.#icon.setIcon(iconName);
      this.#text.textContent = window.WoltLabLanguage.getPhrase(phrase);
    }
  }

  window.customElements.define("woltlab-core-file-upload", WoltlabCoreFileUploadElement);
}
