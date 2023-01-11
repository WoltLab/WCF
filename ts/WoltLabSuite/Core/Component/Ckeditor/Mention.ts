import { prepareRequest } from "../../Ajax/Backend";

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
  icon: string
};

export async function getPossibleMentions(query: string): Promise<UserMention[]> {
  // TODO: Provide the URL as a parameter.
  const url = new URL(window.WSC_API_URL + "index.php?editor-get-mention-suggestions/");
  url.searchParams.set("query", query);

  const result = (await prepareRequest(url.toString())
    .get()
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
