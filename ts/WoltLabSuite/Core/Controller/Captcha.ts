/**
 * Provides data of the active user.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Controller/Captcha
 */

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
};

export = ControllerCaptcha;
