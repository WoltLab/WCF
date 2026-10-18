/**
 * Gets the data of a media file that is required to insert or select it.
 *
 * @author  Marcel Werk
 * @copyright  2001-2026 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "WoltLabSuite/Core/Api/Result";

export type MediaSize = "original" | "small" | "medium" | "large";

export type MediaData = {
  mediaID: number;
  categoryID: number | null;
  title: string;
  altText: string;
  filename: string;
  formattedFilesize: string;
  isImage: 0 | 1;
  link: string;
  elementTag: string;
  editorLinks: Partial<Record<MediaSize, string>>;
  tinyThumbnailLink: string;
  smallThumbnailLink: string;
  mediumThumbnailLink: string;
  largeThumbnailLink: string;
};

export async function getMedia(mediaId: number): Promise<MediaData> {
  const url = new URL(`${window.WSC_RPC_API_URL}core/media/${mediaId}`);

  return fromInfallibleApiRequest(() => {
    return prepareRequest(url).get().fetchAsJson();
  });
}
