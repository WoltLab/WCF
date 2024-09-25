/**
 * Renders the QR code containing the TOTP secret.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle  all
 */
define(["require", "exports", "tslib", "qr-creator", "../../../../Language"], function (require, exports, tslib_1, qr_creator_1, Language) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.render = render;
    exports.renderAll = renderAll;
    qr_creator_1 = tslib_1.__importDefault(qr_creator_1);
    Language = tslib_1.__importStar(Language);
    function render(container) {
        const secret = container.querySelector(".totpSecret");
        if (!secret) {
            return;
        }
        const accountName = secret.dataset.accountname;
        if (!accountName) {
            return;
        }
        const readableIssuer = secret.dataset.issuer || "";
        const label = (readableIssuer !== "" ? `${readableIssuer}:` : "") + accountName;
        const canvas = container.querySelector("canvas");
        if (!canvas) {
            throw new Error("Missing <canvas>.");
        }
        qr_creator_1.default.render({
            text: getUrl(readableIssuer, label, secret.textContent),
            size: canvas.clientWidth,
        }, canvas);
        const a = document.createElement("a");
        a.href = getUrl(window.location.hostname, label, secret.textContent);
        a.ariaLabel = Language.get("wcf.user.security.multifactor.com.woltlab.wcf.multifactor.totp.link");
        canvas.parentElement.insertAdjacentElement("afterbegin", a);
        a.appendChild(canvas);
    }
    function getUrl(issuer, label, secret) {
        return `otpauth://totp/${encodeURIComponent(label)}?secret=${encodeURIComponent(secret)}${issuer !== "" ? `&issuer=${encodeURIComponent(issuer)}` : ""}`;
    }
    exports.default = render;
    function renderAll() {
        document.querySelectorAll(".totpSecretContainer").forEach((el) => render(el));
    }
});
