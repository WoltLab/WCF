/**
 * Provides the media manager dialog for selecting media for input elements.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 * @deprecated 6.3 Use `WoltLabSuite/Core/Component/Media/Select` instead.
 */

import { MediaManagerSelectOptions } from "../Data";
import { setup } from "../../Component/Media/Select";

class MediaManagerSelect {
  constructor(options: Partial<MediaManagerSelectOptions>) {
    document.querySelectorAll<HTMLElement>(`.${options.buttonClass || "jsMediaSelectButton"}`).forEach((button) => {
      if (button.dataset.store) {
        setup(button, {
          imagesOnly: options.imagesOnly,
          title: options.dialogTitle,
        });
      }
    });
  }
}

export = MediaManagerSelect;
