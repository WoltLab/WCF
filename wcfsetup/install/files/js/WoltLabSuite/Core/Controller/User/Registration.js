/**
 * Handles the validation in the registration form.
 *
 * @author    Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Ajax/Backend", "WoltLabSuite/Core/Language", "../../Dom/Util"], function (require, exports, tslib_1, Backend_1, Language_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Util_1 = tslib_1.__importDefault(Util_1);
    async function validateUsername(username, options) {
        const value = username.value.trim();
        if (!value) {
            showErrorMessage(username, "wcf.global.form.error.empty");
            return;
        }
        if (value.length < options.minlength || value.length > options.maxlength) {
            showErrorMessage(username, "wcf.user.username.error.invalid");
            return;
        }
        const result = (await (0, Backend_1.prepareRequest)(username.dataset.validationEndpoint)
            .post({
            username: value,
        })
            .fetchAsJson());
        if (!result.ok) {
            showErrorMessage(username, `wcf.user.username.error.${result.error}`);
            return;
        }
        showSuccessMessage(username);
    }
    async function validateEmail(email) {
        const value = email.value.trim();
        if (!value) {
            showErrorMessage(email, "wcf.global.form.error.empty");
            return;
        }
        const result = (await (0, Backend_1.prepareRequest)(email.dataset.validationEndpoint)
            .post({
            email: value,
        })
            .fetchAsJson());
        if (!result.ok) {
            showErrorMessage(email, `wcf.user.email.error.${result.error}`);
            return;
        }
        showSuccessMessage(email);
    }
    function validatePassword(password) {
        if (!password.value.trim()) {
            showErrorMessage(password, "wcf.global.form.error.empty");
            return;
        }
        // The remaining validation is handled by `PasswordStrength`.
    }
    function showErrorMessage(input, message) {
        const parent = input.closest("dl");
        parent.classList.add("formError");
        parent.classList.remove("formSuccess");
        Util_1.default.innerError(input, (0, Language_1.getPhrase)(message));
    }
    function showSuccessMessage(input) {
        const parent = input.closest("dl");
        parent.classList.remove("formError");
        parent.classList.add("formSuccess");
        Util_1.default.innerError(input);
    }
    function setup(username, email, password, usernameOptions) {
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
});
