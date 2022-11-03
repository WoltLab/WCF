import { dialogFactory } from "../../Component/Dialog";
import { getXsrfToken } from "../../Core";
import * as FormBuilderManager from "../../Form/Builder/Manager";

type Response = {
  dialog: string;
  formId: string;
};

export function setup(button: HTMLElement): void {
  button.addEventListener("click", async (event) => {
    const response = await fetch(button.dataset.url!);
    const json: Response = await response.json();

    const dialog = dialogFactory().fromHtml(json.dialog).asPrompt();
    dialog.addEventListener("primary", async () => {
      const data = await FormBuilderManager.getData(json.formId);

      const response = await fetch(
        button.dataset.url!,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json; charset=UTF-8",
            "X-XSRF-TOKEN": getXsrfToken(),
          },
          body: JSON.stringify(data),
        }
      );

      // TODO: Show success / update UI
      // TODO: Handle incorrect form inputs
    });
    dialog.addEventListener("close", () => {
      // TODO: This appears to be broken
      if (FormBuilderManager.hasForm(json.formId)) {
        FormBuilderManager.unregisterForm(json.formId);
      }
    });
    dialog.show("yadayada");
    reinsertScripts(dialog.content);
  });
}

function reinsertScripts(container: HTMLElement) {
  const scripts = container.querySelectorAll<HTMLScriptElement>("script");
  for (let i = 0, length = scripts.length; i < length; i++) {
    const script = scripts[i];
    const newScript = document.createElement("script");
    if (script.src) {
      newScript.src = script.src;
    } else {
      newScript.textContent = script.textContent;
    }

    container.appendChild(newScript);
    script.remove();
  }
}
