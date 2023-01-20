/**
 * Handles the guest dialog in the comment list.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Component/Comment/GuestDialog
 * @since 6.0
 */

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
    let captchaData: Promise<ArbitraryObject> | ArbitraryObject | undefined = undefined;
    dialog.addEventListener("validate", (event) => {
      if (ControllerCaptcha.has(captchaId)) {
        captchaData = ControllerCaptcha.getData(captchaId) as Promise<ArbitraryObject> | ArbitraryObject;
        ControllerCaptcha.delete(captchaId);

        if (captchaData instanceof Promise) {
          event.detail.push(
            new Promise((resolve) => {
              void (captchaData as Promise<ArbitraryObject>)
                .then(() => {
                  resolve(true);
                })
                .catch(() => {
                  resolve(false);
                });
            }),
          );

          event.preventDefault();
        }
      }
    });

    dialog.addEventListener("primary", () => {
      const parameters = {
        data: {
          username: usernameInput.value,
        },
      };

      if (captchaData !== undefined) {
        if (captchaData instanceof Promise) {
          void captchaData.then((data) => {
            resolve({
              ...parameters,
              ...data,
            });
          });
        } else {
          resolve({
            ...parameters,
            ...captchaData,
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
