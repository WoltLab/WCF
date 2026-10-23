/**
 * Embeds standalone polls into the editor through the poll manager.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */

import { listenToCkeditor } from "./Event";
import { promiseMutex } from "../../Helper/PromiseMutex";
import type { CKEditor } from "../Ckeditor";

async function insertPoll(ckeditor: CKEditor): Promise<void> {
  const { openPollManager } = await import("../Poll/Manager");

  const poll = await openPollManager();
  if (poll === undefined) {
    return;
  }

  ckeditor.insertText(`[poll='${poll.pollID}'][/poll]`);
}

export function setup(element: HTMLElement): void {
  listenToCkeditor(element).ready(({ ckeditor }) => {
    if (!ckeditor.features.poll) {
      return;
    }

    // The poll manager is loaded asynchronously, repeated clicks must not open it twice.
    const openManager = promiseMutex(() => insertPoll(ckeditor));
    listenToCkeditor(ckeditor.sourceElement).bbcode(({ bbcode }) => {
      if (bbcode !== "poll") {
        return false;
      }

      openManager();

      return true;
    });
  });
}
