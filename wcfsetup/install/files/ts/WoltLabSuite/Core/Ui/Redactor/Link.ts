import DomUtil from "../../Dom/Util";
import * as Language from "../../Language";
import UiDialog from "../Dialog";
import { DialogCallbackObject, DialogCallbackSetup } from "../Dialog/Data";

type SubmitCallback = () => boolean;

interface LinkOptions {
  insert: boolean;
  submitCallback: SubmitCallback;
}

class UiRedactorLink implements DialogCallbackObject {
  private boundListener = false;
  private submitCallback: SubmitCallback;

  open(options: LinkOptions) {
    UiDialog.open(this);

    UiDialog.setTitle(this, Language.get("wcf.editor.link." + (options.insert ? "add" : "edit")));

    const submitButton = document.getElementById("redactor-modal-button-action")!;
    submitButton.textContent = Language.get("wcf.global.button." + (options.insert ? "insert" : "save"));

    this.submitCallback = options.submitCallback;

    // Redactor might modify the button, thus we cannot bind it in the dialog's `onSetup()` callback.
    if (!this.boundListener) {
      this.boundListener = true;

      submitButton.addEventListener("click", this.submit.bind(this));
    }
  }

  private submit(): void {
    if (this.submitCallback()) {
      UiDialog.close(this);
    } else {
      const url = document.getElementById("redactor-link-url") as HTMLInputElement;

      const errorMessage = url.value.trim() === "" ? "wcf.global.form.error.empty" : "wcf.editor.link.error.invalid";
      DomUtil.innerError(url, Language.get(errorMessage));
    }
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "redactorDialogLink",
      options: {
        onClose: () => {
          const url = document.getElementById("redactor-link-url") as HTMLInputElement;
          const small = url.nextElementSibling;
          if (small && small.nodeName === "SMALL") {
            small.remove();
          }
        },
        onSetup: (content) => {
          const submitButton = content.querySelector(".formSubmit > .buttonPrimary") as HTMLButtonElement;

          if (submitButton !== null) {
            content.querySelectorAll('input[type="url"], input[type="text"]').forEach((input: HTMLInputElement) => {
              input.addEventListener("keyup", (event) => {
                if (event.key === "Enter") {
                  submitButton.click();
                }
              });
            });
          }
        },
        onShow: () => {
          const url = document.getElementById("redactor-link-url") as HTMLInputElement;
          url.focus();
        },
      },
      source: `<dl>
          <dt>
            <label for="redactor-link-url">${Language.get("wcf.editor.link.url")}</label>
          </dt>
          <dd>
            <input type="url" id="redactor-link-url" class="long">
          </dd>
        </dl>
        <dl>
          <dt>
            <label for="redactor-link-url-text">${Language.get("wcf.editor.link.text")}</label>
          </dt>
          <dd>
            <input type="text" id="redactor-link-url-text" class="long">
          </dd>
        </dl>
        <div class="formSubmit">
          <button id="redactor-modal-button-action" class="buttonPrimary"></button>
        </div>`,
    };
  }
}

let uiRedactorLink: UiRedactorLink;

export function showDialog(options: LinkOptions): void {
  if (!uiRedactorLink) {
    uiRedactorLink = new UiRedactorLink();
  }

  uiRedactorLink.open(options);
}
