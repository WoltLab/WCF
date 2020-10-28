/**
 * Provides buttons to share a page through multiple social community sites.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Message/Share
 */
define(['EventHandler', 'StringUtil'], function (EventHandler, StringUtil) {
    "use strict";
    /**
     * @exports	WoltLabSuite/Core/Ui/Message/Share
     */
    return {
        _pageDescription: '',
        _pageUrl: '',
        init: function () {
            var title = elBySel('meta[property="og:title"]');
            if (title !== null)
                this._pageDescription = encodeURIComponent(title.content);
            var url = elBySel('meta[property="og:url"]');
            if (url !== null)
                this._pageUrl = encodeURIComponent(url.content);
            elBySelAll('.jsMessageShareButtons', null, (function (container) {
                container.classList.remove('jsMessageShareButtons');
                var pageUrl = encodeURIComponent(StringUtil.unescapeHTML(elData(container, 'url') || ''));
                if (!pageUrl) {
                    pageUrl = this._pageUrl;
                }
                var providers = {
                    facebook: {
                        link: elBySel('.jsShareFacebook', container),
                        share: (function (event) {
                            event.preventDefault();
                            this._share('facebook', 'https://www.facebook.com/sharer.php?u={pageURL}&t={text}', true, pageUrl);
                        }).bind(this)
                    },
                    google: {
                        link: elBySel('.jsShareGoogle', container),
                        share: (function (event) {
                            event.preventDefault();
                            this._share('google', 'https://plus.google.com/share?url={pageURL}', false, pageUrl);
                        }).bind(this)
                    },
                    reddit: {
                        link: elBySel('.jsShareReddit', container),
                        share: (function (event) {
                            event.preventDefault();
                            this._share('reddit', 'https://ssl.reddit.com/submit?url={pageURL}', false, pageUrl);
                        }).bind(this)
                    },
                    twitter: {
                        link: elBySel('.jsShareTwitter', container),
                        share: (function (event) {
                            event.preventDefault();
                            this._share('twitter', 'https://twitter.com/share?url={pageURL}&text={text}', false, pageUrl);
                        }).bind(this)
                    },
                    linkedIn: {
                        link: elBySel('.jsShareLinkedIn', container),
                        share: (function (event) {
                            event.preventDefault();
                            this._share('linkedIn', 'https://www.linkedin.com/cws/share?url={pageURL}', false, pageUrl);
                        }).bind(this)
                    },
                    pinterest: {
                        link: elBySel('.jsSharePinterest', container),
                        share: (function (event) {
                            event.preventDefault();
                            this._share('pinterest', 'https://www.pinterest.com/pin/create/link/?url={pageURL}&description={text}', false, pageUrl);
                        }).bind(this)
                    },
                    xing: {
                        link: elBySel('.jsShareXing', container),
                        share: (function (event) {
                            event.preventDefault();
                            this._share('xing', 'https://www.xing.com/social_plugins/share?url={pageURL}', false, pageUrl);
                        }).bind(this)
                    },
                    whatsApp: {
                        link: elBySel('.jsShareWhatsApp', container),
                        share: (function (event) {
                            event.preventDefault();
                            window.location.href = 'https://api.whatsapp.com/send?text=' + this._pageDescription + '%20' + this._pageUrl;
                        }).bind(this)
                    }
                };
                EventHandler.fire('com.woltlab.wcf.message.share', 'shareProvider', {
                    container: container,
                    providers: providers,
                    pageDescription: this._pageDescription,
                    pageUrl: this._pageUrl
                });
                for (var provider in providers) {
                    if (providers.hasOwnProperty(provider)) {
                        if (providers[provider].link !== null) {
                            providers[provider].link.addEventListener(WCF_CLICK_EVENT, providers[provider].share);
                        }
                    }
                }
            }).bind(this));
        },
        _share: function (objectName, url, appendUrl, pageUrl) {
            // fallback for plugins
            if (!pageUrl) {
                pageUrl = this._pageUrl;
            }
            window.open(url.replace(/\{pageURL}/, pageUrl).replace(/\{text}/, this._pageDescription + (appendUrl ? "%20" + pageUrl : "")), objectName, 'height=600,width=600');
        }
    };
});
