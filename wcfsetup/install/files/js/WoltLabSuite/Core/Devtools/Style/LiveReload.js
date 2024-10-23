/**
 * Schedules a live reload of the style's CSS.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle  all
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.watch = watch;
    exports.notify = notify;
    const channelName = "com.woltlab.wcf#DevTools/Style/LiveReload";
    function watch() {
        if (!window.BroadcastChannel) {
            return;
        }
        const channel = new BroadcastChannel(channelName);
        channel.onmessage = (_ev) => {
            const link = document.querySelector("head link[rel=stylesheet]");
            if (!link) {
                return;
            }
            const url = new URL(link.href);
            url.searchParams.set("m", Math.trunc(Date.now() / 1_000).toString());
            const newLink = document.createElement("link");
            newLink.rel = "stylesheet";
            newLink.addEventListener("load", () => {
                link.remove();
            });
            newLink.addEventListener("error", () => {
                newLink.remove();
            });
            newLink.href = url.toString();
            link.insertAdjacentElement("afterend", newLink);
        };
    }
    function notify() {
        if (!window.BroadcastChannel) {
            return;
        }
        const channel = new BroadcastChannel(channelName);
        channel.postMessage(undefined);
    }
});
