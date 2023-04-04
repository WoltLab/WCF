/**
 * Provides mention support for users and groups.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import { prepareRequest } from "../../Ajax/Backend";
import { createFragmentFromHtml } from "../../Dom/Util";

import type { MentionConfig } from "@ckeditor/ckeditor5-mention/src/mention";
import { listenToCkeditor } from "./Event";

type SearchResultItem = {
  avatarTag: string;
  username: string;
  userID: number;
  //  type: "group" | "user";
};
type ResultGetSearchResultList = SearchResultItem[];

type UserMention = {
  id: string;
  text: string;
  icon: string;
};

async function getPossibleMentions(query: string): Promise<UserMention[]> {
  // TODO: Provide the URL as a parameter.
  const url = new URL(window.WSC_API_URL + "index.php?editor-get-mention-suggestions/");
  url.searchParams.set("query", query);

  const result = (await prepareRequest(url.toString())
    .get()
    .allowCaching()
    .disableLoadingIndicator()
    .fetchAsJson()) as ResultGetSearchResultList;

  return result.map((item) => {
    return {
      id: `@${item.username}`,
      text: item.username,
      icon: item.avatarTag,
    };
  });
}

function getMentionConfiguration(): MentionConfig {
  return {
    feeds: [
      {
        feed: (query) => {
          // TODO: The typings are outdated, cast the result to `any`.
          return getPossibleMentions(query) as any;
        },
        itemRenderer: (item: Awaited<ReturnType<typeof getPossibleMentions>>[0]) => {
          // TODO: This is ugly.
          return createFragmentFromHtml(`
            <span>${item.icon} ${item.text}</span>
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
