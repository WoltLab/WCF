define(["require", "exports", "tslib", "qr-creator"], function (require, exports, tslib_1, qr_creator_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.renderAll = exports.render = void 0;
    qr_creator_1 = tslib_1.__importDefault(qr_creator_1);
    function render(container) {
        const secret = container.querySelector(".totpSecret");
        if (!secret) {
            return;
        }
        const accountName = secret.dataset.accountname;
        if (!accountName) {
            return;
        }
        const issuer = secret.dataset.issuer;
        const label = (issuer ? `${issuer}:` : "") + accountName;
        qr_creator_1.default.render({
            text: `otpauth://totp/${encodeURIComponent(label)}?secret=${encodeURIComponent(secret.textContent)}${issuer ? `&issuer=${encodeURIComponent(issuer)}` : ""}`,
        }, container);
    }
    exports.render = render;
    exports.default = render;
    function renderAll() {
        document.querySelectorAll(".totpSecretContainer").forEach((el) => render(el));
    }
    exports.renderAll = renderAll;
});
