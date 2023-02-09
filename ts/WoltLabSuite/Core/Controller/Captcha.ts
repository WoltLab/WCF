/**
 * Provides data of the active user.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import type WoltlabCoreDialogElement from "../Element/woltlab-core-dialog";

type CallbackCaptcha = () => unknown;

const _captchas = new Map<string, CallbackCaptcha>();

const ControllerCaptcha = {
  /**
   * Registers a captcha with the given identifier and callback used to get captcha data.
   */
  add(captchaId: string, callback: CallbackCaptcha): void {
    if (_captchas.has(captchaId)) {
      throw new Error(`Captcha with id '${captchaId}' is already registered.`);
    }

    if (typeof callback !== "function") {
      throw new TypeError("Expected a valid callback for parameter 'callback'.");
    }

    _captchas.set(captchaId, callback);
  },

  /**
   * Deletes the captcha with the given identifier.
   */
  delete(captchaId: string): void {
    if (!_captchas.has(captchaId)) {
      throw new Error(`Unknown captcha with id '${captchaId}'.`);
    }

    _captchas.delete(captchaId);
  },

  /**
   * Returns true if a captcha with the given identifier exists.
   */
  has(captchaId: string): boolean {
    return _captchas.has(captchaId);
  },

  /**
   * Returns the data of the captcha with the given identifier.
   *
   * @param  {string}  captchaId  captcha identifier
   * @return  {Object}  captcha data
   */
  getData(captchaId: string): unknown {
    if (!_captchas.has(captchaId)) {
      throw new Error(`Unknown captcha with id '${captchaId}'.`);
    }

    return _captchas.get(captchaId)!();
  },

  setupDialog(dialog: WoltlabCoreDialogElement, captchaId: string): Promise<ArbitraryObject> {
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

    return new Promise((resolve) => {
      dialog.addEventListener("primary", () => {
        if (captchaData === undefined) {
          resolve({});
          return;
        }

        if (captchaData instanceof Promise) {
          void captchaData.then((data) => {
            resolve(data);
          });
        } else {
          resolve(captchaData);
        }
      });
    });
  },
};

export = ControllerCaptcha;
