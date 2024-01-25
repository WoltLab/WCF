/**
 * Shared cache for popover instances serving the same selector.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";

type ObjectId = number;

export class SharedCache {
  readonly #data = new Map<ObjectId, string>();
  readonly #endpoint: URL;

  constructor(endpoint: string) {
    this.#endpoint = new URL(endpoint);
  }

  async get(objectId: ObjectId): Promise<string> {
    let content = this.#data.get(objectId);
    if (content !== undefined) {
      return content;
    }

    this.#endpoint.searchParams.set("id", objectId.toString());

    const response = await prepareRequest(this.#endpoint).get().fetchAsResponse();
    if (!response?.ok) {
      return "";
    }

    content = await response.text();
    this.#data.set(objectId, content);

    return content;
  }

  reset(objectId: ObjectId): void {
    this.#data.delete(objectId);
  }
}

export default SharedCache;
