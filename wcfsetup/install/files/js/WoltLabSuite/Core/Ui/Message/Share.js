/**
 * Provides buttons to share a page through multiple social community sites.
 *
 * @author  Marcel Werk
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Message/Share
 */
define(["require", "exports", "tslib", "../../Event/Handler", "../../StringUtil"], function (require, exports, tslib_1, EventHandler, StringUtil) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;
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
                    link: container.querySelector(".jsShareFacebook"),
                    share(event) {
                        event.preventDefault();
                        share("facebook", "https://www.facebook.com/sharer.php?u={pageURL}&t={text}", true, pageUrl);
                    },
                },
                google: {
                    link: container.querySelector(".jsShareGoogle"),
                    share(event) {
                        event.preventDefault();
                        share("google", "https://plus.google.com/share?url={pageURL}", false, pageUrl);
                    },
                },
                reddit: {
                    link: container.querySelector(".jsShareReddit"),
                    share(event) {
                        event.preventDefault();
                        share("reddit", "https://ssl.reddit.com/submit?url={pageURL}", false, pageUrl);
                    },
                },
                twitter: {
                    link: container.querySelector(".jsShareTwitter"),
                    share(event) {
                        event.preventDefault();
                        share("twitter", "https://twitter.com/share?url={pageURL}&text={text}", false, pageUrl);
                    },
                },
                linkedIn: {
                    link: container.querySelector(".jsShareLinkedIn"),
                    share(event) {
                        event.preventDefault();
                        share("linkedIn", "https://www.linkedin.com/cws/share?url={pageURL}", false, pageUrl);
                    },
                },
                pinterest: {
                    link: container.querySelector(".jsSharePinterest"),
                    share(event) {
                        event.preventDefault();
                        share("pinterest", "https://www.pinterest.com/pin/create/link/?url={pageURL}&description={text}", false, pageUrl);
                    },
                },
                xing: {
                    link: container.querySelector(".jsShareXing"),
                    share(event) {
                        event.preventDefault();
                        share("xing", "https://www.xing.com/social_plugins/share?url={pageURL}", false, pageUrl);
                    },
                },
                whatsApp: {
                    link: container.querySelector(".jsShareWhatsApp"),
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
    exports.init = init;
});
