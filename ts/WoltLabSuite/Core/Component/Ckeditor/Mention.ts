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
import { createFragmentFromHtml } from "../../Dom/Util";
import { listenToCkeditor } from "./Event";
import { mentionSuggestions } from "WoltLabSuite/Core/Api/Messages/MentionSuggestions";

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

  return (await mentionSuggestions(query)).unwrap().map((item) => {
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
