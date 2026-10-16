/**
 * Handles editing media files via dialog.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */

import { Media, MediaEditorCallbackObject } from "./Data";
import { dialogFactory } from "../Component/Dialog";
import { showDefaultSuccessSnackbar } from "../Component/Snackbar";

type MediaEditResult = Pick<Media, "mediaID" | "categoryID" | "title" | "filename" | "elementTag">;

let endpoint = "";

class MediaEditor {
  protected readonly _callbackObject: MediaEditorCallbackObject;
  protected _media: Media | null = null;

  constructor(callbackObject: MediaEditorCallbackObject) {
    this._callbackObject = callbackObject || {};

    if (this._callbackObject._editorClose && typeof this._callbackObject._editorClose !== "function") {
      throw new TypeError("Callback object has no function '_editorClose'.");
    }
    if (this._callbackObject._editorSuccess && typeof this._callbackObject._editorSuccess !== "function") {
      throw new TypeError("Callback object has no function '_editorSuccess'.");
    }
  }

  /**
   * Sets the link of the edit dialog, `%s` is replaced with the id of the
   * media file.
   *
   * @since 6.3
   */
  public static setEndpoint(link: string): void {
    endpoint = link;
  }

  /**
   * Edits the media with the given data or id.
   */
  public async edit(editedMedia: Media | number): Promise<void> {
    const media: Media =
      typeof editedMedia === "object" ? editedMedia : ({ mediaID: editedMedia, categoryID: 0 } as Media);

    if (this._media !== null) {
      throw new Error(
        `Cannot edit media with id ${media.mediaID} while editing media with id '${this._media.mediaID}'.`,
      );
    }

    if (endpoint === "") {
      throw new Error("The endpoint of the media editor has not been set.");
    }

    this._media = media;

    const { ok, result } = await dialogFactory()
      .usingFormBuilder()
      .fromEndpoint<MediaEditResult>(endpoint.replace("%s", media.mediaID.toString()));

    this._media = null;

    if (!ok) {
      this._callbackObject._editorClose?.();

      return;
    }

    showDefaultSuccessSnackbar();

    this._callbackObject._editorSuccess?.({ ...media, ...result }, media.categoryID);
  }

  /**
   * Updates the data of the currently edited media file.
   */
  public updateData(media: Media): void {
    if (this._callbackObject._editorSuccess) {
      this._callbackObject._editorSuccess(media, undefined, false);
    }
  }
}

export = MediaEditor;
