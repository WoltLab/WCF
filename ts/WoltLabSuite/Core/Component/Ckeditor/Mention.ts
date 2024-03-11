/**
 * Provides mention support for users and groups.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @woltlabExcludeBundle tiny
 */

import type { CKEditor5 } from "@woltlab/editor";
import { prepareRequest } from "../../Ajax/Backend";
import { createFragmentFromHtml } from "../../Dom/Util";
import { listenToCkeditor } from "./Event";

type SearchResultItem =
  | {
      avatarTag: string;
      username: string;
      userID: number;
      type: "user";
    }
  | {
      name: string;
      groupID: string;
      type: "group";
    };
type ResultGetSearchResultList = SearchResultItem[];

type Mention = {
  id: string;
  text: string;
  icon: string;
};

async function getPossibleMentions(query: string): Promise<Mention[]> {
  // Prevent excessive attempts to resolve mentions.
  if (query.length > 24) {
    return [];
  }

  // TODO: Provide the URL as a parameter.
  const url = new URL(window.WSC_API_URL + "index.php?api/rpc/core/messages/mentionsuggestions");
  url.searchParams.set("query", query);

  const result = (await prepareRequest(url.toString())
    .get()
    .allowCaching()
    .disableLoadingIndicator()
    .fetchAsJson()) as ResultGetSearchResultList;

  return result.map((item) => {
    if (item.type === "user") {
      return {
        id: `@${item.username}`,
        text: `@${item.username}`,
        icon: item.avatarTag,
        objectId: item.userID,
        type: item.type,
      };
    } else {
      return {
        id: `@${item.name}`,
        text: `@${item.name}`,
        icon: '<fa-icon name="users"></fa-icon>',
        objectId: item.groupID,
        type: item.type,
      };
    }
  });
}

function getMentionConfiguration(): CKEditor5.Mention.MentionConfig {
  return {
    feeds: [
      {
        feed: (query) => getPossibleMentions(query),
        itemRenderer: (item: Awaited<ReturnType<typeof getPossibleMentions>>[0]) => {
          return createFragmentFromHtml(`
            <span class="ckeditor5__mention">${item.icon} ${item.text}</span>
          `).firstElementChild as HTMLElement;
        },
        marker: "@",
        minimumCharacters: 3,
      },
    ],
  };
}

export function setup(element: HTMLElement): void {
  listenToCkeditor(element).setupConfiguration(({ configuration, features }) => {
    if (!features.mention) {
      return;
    }

    configuration.mention = getMentionConfiguration();
  });
}
