/**
 * Provides buttons to share a page through multiple social community sites.
 *
 * @author  Marcel Werk
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Message/Share
 */

import * as EventHandler from "../../Event/Handler";
import * as StringUtil from "../../StringUtil";

let _pageDescription = "";
let _pageUrl = "";

function share(objectName: string, url: string, appendUrl: boolean, pageUrl: string) {
  // fallback for plugins
  if (!pageUrl) {
    pageUrl = _pageUrl;
  }

  window.open(
    url.replace("{pageURL}", pageUrl).replace("{text}", _pageDescription + (appendUrl ? `%20${pageUrl}` : "")),
    objectName,
    "height=600,width=600",
  );
}

interface Provider {
  link: HTMLElement | null;

  share(event: MouseEvent): void;
}

interface Providers {
  [key: string]: Provider;
}

export function init(): void {
  const title = document.querySelector('meta[property="og:title"]') as HTMLMetaElement;
  if (title !== null) {
    _pageDescription = encodeURIComponent(title.content);
  }

  const url = document.querySelector('meta[property="og:url"]') as HTMLMetaElement;
  if (url !== null) {
    _pageUrl = encodeURIComponent(url.content);
  }

  document.querySelectorAll(".jsMessageShareButtons").forEach((container: HTMLElement) => {
    container.classList.remove("jsMessageShareButtons");

    let pageUrl = encodeURIComponent(StringUtil.unescapeHTML(container.dataset.url || ""));
    if (!pageUrl) {
      pageUrl = _pageUrl;
    }

    const providers: Providers = {
      facebook: {
        link: container.querySelector(".jsShareFacebook"),
        share(event: MouseEvent): void {
          event.preventDefault();
          share("facebook", "https://www.facebook.com/sharer.php?u={pageURL}&t={text}", true, pageUrl);
        },
      },
      google: {
        link: container.querySelector(".jsShareGoogle"),
        share(event: MouseEvent): void {
          event.preventDefault();
          share("google", "https://plus.google.com/share?url={pageURL}", false, pageUrl);
        },
      },
      reddit: {
        link: container.querySelector(".jsShareReddit"),
        share(event: MouseEvent): void {
          event.preventDefault();
          share("reddit", "https://ssl.reddit.com/submit?url={pageURL}", false, pageUrl);
        },
      },
      twitter: {
        link: container.querySelector(".jsShareTwitter"),
        share(event: MouseEvent): void {
          event.preventDefault();
          share("twitter", "https://twitter.com/share?url={pageURL}&text={text}", false, pageUrl);
        },
      },
      linkedIn: {
        link: container.querySelector(".jsShareLinkedIn"),
        share(event: MouseEvent): void {
          event.preventDefault();
          share("linkedIn", "https://www.linkedin.com/cws/share?url={pageURL}", false, pageUrl);
        },
      },
      pinterest: {
        link: container.querySelector(".jsSharePinterest"),
        share(event: MouseEvent): void {
          event.preventDefault();
          share(
            "pinterest",
            "https://www.pinterest.com/pin/create/link/?url={pageURL}&description={text}",
            false,
            pageUrl,
          );
        },
      },
      xing: {
        link: container.querySelector(".jsShareXing"),
        share(event: MouseEvent): void {
          event.preventDefault();
          share("xing", "https://www.xing.com/social_plugins/share?url={pageURL}", false, pageUrl);
        },
      },
      whatsApp: {
        link: container.querySelector(".jsShareWhatsApp"),
        share(event: MouseEvent): void {
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
        const link = provider.link as HTMLAnchorElement;
        link.addEventListener("click", (ev) => provider.share(ev));
      }
    });
  });
}
