/**
 * Manages the share providers shown in the share dialogs.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Message/Share/Providers
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getEnabledProviders = exports.getProviders = exports.enableShareProviders = exports.addShareProvider = void 0;
    const enabledProviders = new Set();
    const providers = new Map([
        [
            "Facebook",
            {
                cssClass: "jsShareFacebook",
                iconClassName: "fa-facebook-official",
                label: "wcf.message.share.facebook",
            },
        ],
        [
            "Twitter",
            {
                cssClass: "jsShareTwitter",
                iconClassName: "fa-twitter",
                label: "wcf.message.share.twitter",
            },
        ],
        [
            "Reddit",
            {
                cssClass: "jsShareReddit",
                iconClassName: "fa-reddit",
                label: "wcf.message.share.reddit",
            },
        ],
        [
            "WhatsApp",
            {
                cssClass: "jsShareWhatsApp",
                iconClassName: "fa-whatsapp",
                label: "wcf.message.share.whatsApp",
            },
        ],
        [
            "LinkedIn",
            {
                cssClass: "jsShareLinkedIn",
                iconClassName: "fa-linkedin",
                label: "wcf.message.share.linkedIn",
            },
        ],
        [
            "Pinterest",
            {
                cssClass: "jsSharePinterest",
                iconClassName: "fa-pinterest-p",
                label: "wcf.message.share.pinterest",
            },
        ],
        [
            "XING",
            {
                cssClass: "jsShareXing",
                iconClassName: "fa-xing",
                label: "wcf.message.share.xing",
            },
        ],
    ]);
    function addShareProvider(providerName, provider) {
        if (providers.has(providerName)) {
            throw new Error(`A share provider with name "${providerName}" already exists.`);
        }
        providers.set(providerName, provider);
    }
    exports.addShareProvider = addShareProvider;
    function enableShareProviders(providerNames) {
        providerNames.forEach((providerName) => {
            if (providers.has(providerName)) {
                enabledProviders.add(providers.get(providerName));
            }
        });
    }
    exports.enableShareProviders = enableShareProviders;
    function getProviders() {
        return providers;
    }
    exports.getProviders = getProviders;
    function getEnabledProviders() {
        return enabledProviders;
    }
    exports.getEnabledProviders = getEnabledProviders;
});
