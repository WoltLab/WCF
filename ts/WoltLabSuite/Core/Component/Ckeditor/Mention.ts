import { prepareRequest } from "../../Ajax/Backend";
import { createFragmentFromHtml } from "../../Dom/Util";

import type { MentionConfig } from "@ckeditor/ckeditor5-mention/src/mention";

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

export async function getPossibleMentions(query: string): Promise<UserMention[]> {
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

export function getMentionConfiguration(): MentionConfig {
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
