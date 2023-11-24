/**
 * Handles the validation in the registration form.
 *
 * @author    Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { getPhrase } from "WoltLabSuite/Core/Language";
import DomUtil from "../../Dom/Util";

type ValidationResult =
  | {
      ok: true;
    }
  | {
      ok: false;
      error: string;
    };

type UsernameOptions = {
  minlength: number;
  maxlength: number;
};

async function validateUsername(username: HTMLInputElement, options: UsernameOptions): Promise<void> {
  const value = username.value.trim();

  if (!value) {
    showErrorMessage(username, "wcf.global.form.error.empty");
    return;
  }

  if (value.length < options.minlength || value.length > options.maxlength) {
    showErrorMessage(username, "wcf.user.username.error.invalid");
    return;
  }

  const result = (await prepareRequest(username.dataset.validationEndpoint!)
    .post({
      username: value,
    })
    .fetchAsJson()) as ValidationResult;
  if (!result.ok) {
    showErrorMessage(username, `wcf.user.username.error.${result.error}`);
    return;
  }

  showSuccessMessage(username);
}

async function validateEmail(email: HTMLInputElement): Promise<void> {
  const value = email.value.trim();

  if (!value) {
    showErrorMessage(email, "wcf.global.form.error.empty");
    return;
  }

  const result = (await prepareRequest(email.dataset.validationEndpoint!)
    .post({
      email: value,
    })
    .fetchAsJson()) as ValidationResult;
  if (!result.ok) {
    showErrorMessage(email, `wcf.user.email.error.${result.error}`);
    return;
  }

  showSuccessMessage(email);
}

function validatePassword(password: HTMLInputElement): void {
  if (!password.value.trim()) {
    showErrorMessage(password, "wcf.global.form.error.empty");
    return;
  }

  // The remaining validation is handled by `PasswordStrength`.
}

function showErrorMessage(input: HTMLInputElement, message: string): void {
  const parent = input.closest("dl")!;

  parent.classList.add("formError");
  parent.classList.remove("formSuccess");

  DomUtil.innerError(input, getPhrase(message));
}

function showSuccessMessage(input: HTMLInputElement): void {
  const parent = input.closest("dl")!;

  parent.classList.remove("formError");
  parent.classList.add("formSuccess");

  DomUtil.innerError(input);
}

export function setup(
  username: HTMLInputElement,
  email: HTMLInputElement,
  password: HTMLInputElement,
  usernameOptions: UsernameOptions,
): void {
  username.addEventListener("blur", () => {
    void validateUsername(username, usernameOptions);
  });
  email.addEventListener("blur", () => {
    void validateEmail(email);
  });
  password.addEventListener("blur", () => {
    void validatePassword(password);
  });
}
