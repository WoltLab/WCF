/**
 * Switches between trophy types, automatic awarding of
 * trophies and initialized the badge editor.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { setup as setupBadgeEditor } from "./Badge";

const enum TrophyType {
  Image = "1",
  Badge = "2",
}

function setupTypeChange(): void {
  const badgeContainer = document.getElementById("badgeContainer")!;
  const imageContainer = document.getElementById("imageContainer")!;

  const typeSelection = document.querySelector("select[name=type]") as HTMLSelectElement;
  typeSelection.addEventListener("change", () => {
    if (typeSelection.value as TrophyType === TrophyType.Image) {
      badgeContainer.hidden = true;
      imageContainer.hidden = false;
    } else if (typeSelection.value as TrophyType === TrophyType.Badge) {
      badgeContainer.hidden = false;
      imageContainer.hidden = true;
    }
  });
}

function setupAwardConditions(): void {
  const awardAutomatically = document.querySelector("input[name=awardAutomatically]") as HTMLInputElement;
  const revokeContainer = document.getElementById("revokeAutomaticallyDL")!;
  const revokeCheckbox = revokeContainer.querySelector("input")!;

  awardAutomatically.addEventListener("change", () => {
    document.querySelectorAll(".conditionSection").forEach((section: HTMLElement) => {
      if (awardAutomatically.checked) {
        section.hidden = false;
      } else {
        section.hidden = true;
      }
    });

    if (awardAutomatically) {
      revokeContainer.classList.remove("disabled");
      revokeCheckbox.disabled = false;
    } else {
      revokeContainer.classList.add("disabled");
      revokeCheckbox.disabled = true;
      revokeCheckbox.checked = false;
    }
  });
}

export function setup(): void {
  setupTypeChange();
  setupAwardConditions();
  setupBadgeEditor();
}
