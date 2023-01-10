import { dboAction } from "../../Ajax";

import type { MentionFeedItem } from "@ckeditor/ckeditor5-mention/src/mention";

type SearchResultItem = {
  icon: string;
  label: string;
  objectID: string;
  type: "group" | "user";
};
type ResultGetSearchResultList = SearchResultItem[];

export async function getPossibleMentions(query: string): Promise<MentionFeedItem[]> {
  const result = (await dboAction("getSearchResultList", "wcf\\data\\user\\UserAction")
    .disableLoadingIndicator()
    .payload({
      data: {
        includeUserGroups: false, // TODO: Allow groups.
        scope: "mention",
        searchString: query,
      },
    })
    .dispatch()) as ResultGetSearchResultList;

  return result.map((item) => {
    return {
      id: `@${item.label}`,
      text: item.label,
    };
  });
}
