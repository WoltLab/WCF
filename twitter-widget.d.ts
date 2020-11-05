declare namespace twttr {
  export type CallbackReady = () => void;

  export function ready(callback: CallbackReady): void;

  interface Widgets {
    createTweet(tweetId: string, container: HTMLElement, options: ArbitraryObject): Promise<HTMLElement>;
  }

  export const widgets: Widgets;
}
