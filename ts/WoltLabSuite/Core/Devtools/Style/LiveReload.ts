/**
 * Schedules a live reload of the style's CSS.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Devtools/Style/LiveReload
 * @woltlabExcludeBundle=all
 */

const channelName = "com.woltlab.wcf#DevTools/Style/LiveReload";

type UpdateMessage = undefined;

export function watch(): void {
  if (!window.BroadcastChannel) {
    return;
  }

  const channel = new BroadcastChannel(channelName);

  channel.onmessage = (_ev: MessageEvent<UpdateMessage>) => {
    const link: HTMLLinkElement | null = document.querySelector("head link[rel=stylesheet]");
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

export function notify(): void {
  if (!window.BroadcastChannel) {
    return;
  }

  const channel = new BroadcastChannel(channelName);
  channel.postMessage(undefined);
}
