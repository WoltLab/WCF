/**
 * Provides buttons to share a page through multiple social community sites.
 *
 * @author  Marcel Werk
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.0 code is integrated in `Share/Dialog.ts`
 */
define(["require", "exports", "tslib", "../../Event/Handler", "../../StringUtil"], function (require, exports, tslib_1, EventHandler, StringUtil) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    EventHandler = tslib_1.__importStar(EventHandler);
    StringUtil = tslib_1.__importStar(StringUtil);
    let _pageDescription = "";
    let _pageUrl = "";
    function share(objectName, url, appendUrl, pageUrl) {
        // fallback for plugins
        if (!pageUrl) {
            pageUrl = _pageUrl;
        }
        window.open(url.replace("{pageURL}", pageUrl).replace("{text}", _pageDescription + (appendUrl ? `%20${pageUrl}` : "")), objectName, "height=600,width=600");
    }
    function init() {
        const title = document.querySelector('meta[property="og:title"]');
        if (title !== null) {
            _pageDescription = encodeURIComponent(title.content);
        }
        const url = document.querySelector('meta[property="og:url"]');
        if (url !== null) {
            _pageUrl = encodeURIComponent(url.content);
        }
        document.querySelectorAll(".jsMessageShareButtons").forEach((container) => {
            container.classList.remove("jsMessageShareButtons");
            let pageUrl = encodeURIComponent(StringUtil.unescapeHTML(container.dataset.url || ""));
            if (!pageUrl) {
                pageUrl = _pageUrl;
            }
            const providers = {
                facebook: {
                    link: container.querySelector('.messageShareProvider[data-identifier="Facebook"]'),
                    share(event) {
                        event.preventDefault();
                        share("facebook", "https://www.facebook.com/sharer.php?u={pageURL}&t={text}", true, pageUrl);
                    },
                },
                reddit: {
                    link: container.querySelector('.messageShareProvider[data-identifier="Reddit"]'),
                    share(event) {
                        event.preventDefault();
                        share("reddit", "https://ssl.reddit.com/submit?url={pageURL}", false, pageUrl);
                    },
                },
                twitter: {
                    link: container.querySelector('.messageShareProvider[data-identifier="Twitter"]'),
                    share(event) {
                        event.preventDefault();
                        share("twitter", "https://twitter.com/share?url={pageURL}&text={text}", false, pageUrl);
                    },
                },
                linkedIn: {
                    link: container.querySelector('.messageShareProvider[data-identifier="LinkedIn"]'),
                    share(event) {
                        event.preventDefault();
                        share("linkedIn", "https://www.linkedin.com/cws/share?url={pageURL}", false, pageUrl);
                    },
                },
                pinterest: {
                    link: container.querySelector('.messageShareProvider[data-identifier="Pinterest"]'),
                    share(event) {
                        event.preventDefault();
                        share("pinterest", "https://www.pinterest.com/pin/create/link/?url={pageURL}&description={text}", false, pageUrl);
                    },
                },
                xing: {
                    link: container.querySelector('.messageShareProvider[data-identifier="XING"]'),
                    share(event) {
                        event.preventDefault();
                        share("xing", "https://www.xing.com/social_plugins/share?url={pageURL}", false, pageUrl);
                    },
                },
                whatsApp: {
                    link: container.querySelector('.messageShareProvider[data-identifier="WhatsApp"]'),
                    share(event) {
                        event.preventDefault();
                        window.location.href = "https://api.whatsapp.com/send?text=" + _pageDescription + "%20" + _pageUrl;
                    },
                },
            };
            EventHandler.fire("com.woltlab.wcf.message.share", "shareProvider", {
                container,
                providers,
                pageDescription: _pageDescription,
                pageUrl: _pageUrl,
            });
            Object.values(providers).forEach((provider) => {
                if (provider.link !== null) {
                    const link = provider.link;
                    link.addEventListener("click", (ev) => provider.share(ev));
                }
            });
        });
    }
});
