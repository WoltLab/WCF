{
  class WoltlabCoreFileUploadElement extends HTMLElement {
    readonly #element: HTMLInputElement;

    constructor() {
      super();

      this.#element = document.createElement("input");
      this.#element.type = "file";

      this.#element.addEventListener("change", () => {
        const { files } = this.#element;
        if (files === null || files.length === 0) {
          return;
        }

        for (const file of files) {
          const event = new CustomEvent<File>("shouldUpload", {
            cancelable: true,
            detail: file,
          });
          this.dispatchEvent(event);

          if (event.defaultPrevented) {
            continue;
          }

          const uploadEvent = new CustomEvent<File>("upload", {
            detail: file,
          });
          this.dispatchEvent(uploadEvent);
        }
      });
    }

    connectedCallback() {
      const shadow = this.attachShadow({ mode: "open" });
      shadow.append(this.#element);

      const style = document.createElement("style");
      style.textContent = `
        :host {
            position: relative;
        }

        input {
            inset: 0;
            position: absolute;
            visibility: hidden;
        }
      `;
    }
  }

  window.customElements.define("woltlab-core-file-upload", WoltlabCoreFileUploadElement);
}
