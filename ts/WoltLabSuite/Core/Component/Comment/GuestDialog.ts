import ControllerCaptcha from "../../Controller/Captcha";
import { getPhrase } from "../../Language";
import { dialogFactory } from "../Dialog";

export function showGuestDialog(template: string): Promise<Record<string, unknown> | undefined> {
  const captchaId = "commentAdd";
  const dialog = dialogFactory().fromHtml(template).asPrompt();
  dialog.show(getPhrase("wcf.comment.guestDialog.title"));

  const usernameInput = dialog.content.querySelector("input[name=username]") as HTMLInputElement;
  dialog.incomplete = usernameInput.value.trim() === "";
  usernameInput.addEventListener("input", () => {
    dialog.incomplete = usernameInput.value.trim() === "";
  });

  dialog.addEventListener("afterClose", () => {
    if (ControllerCaptcha.has(captchaId)) {
      ControllerCaptcha.delete(captchaId);
    }
  });

  return new Promise((resolve) => {
    dialog.addEventListener("primary", () => {
      const parameters = {
        data: {
          username: usernameInput.value,
        },
      };

      if (ControllerCaptcha.has(captchaId)) {
        const data = ControllerCaptcha.getData(captchaId);
        ControllerCaptcha.delete(captchaId);
        if (data instanceof Promise) {
          void data.then((data) => {
            resolve({
              ...parameters,
              ...data,
            });
          });
        } else {
          resolve({
            ...parameters,
            ...(data as ArbitraryObject),
          });
        }
      } else {
        resolve(parameters);
      }
    });

    dialog.addEventListener("cancel", () => {
      resolve(undefined);
    });
  });
}
