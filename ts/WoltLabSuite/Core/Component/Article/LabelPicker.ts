/**
 * Toggles the visibility of label groups based on the selected category.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */

type CategoryId = number;
type LabelGroupId = number;

function toggleVisibility(showLabelGroupIds: LabelGroupId[] | undefined): void {
  if (showLabelGroupIds === undefined) {
    showLabelGroupIds = [];
  }

  document.querySelectorAll("woltlab-core-label-picker").forEach((labelPicker) => {
    const groupId = parseInt(labelPicker.dataset.groupId!);
    if (showLabelGroupIds!.includes(groupId)) {
      labelPicker.disabled = false;
      labelPicker.closest("dl")!.hidden = false;
    } else {
      labelPicker.disabled = true;
      labelPicker.closest("dl")!.hidden = true;
    }
  });
}

export function setup(categoryMapping: Map<CategoryId, LabelGroupId[]>): void {
  if (categoryMapping.size === 0) {
    return;
  }

  const categoryId = document.getElementById("categoryID") as HTMLSelectElement;
  function updateVisibility() {
    const value = parseInt(categoryId.value);
    toggleVisibility(categoryMapping.get(value));
  }

  categoryId.addEventListener("change", () => {
    updateVisibility();
  });

  updateVisibility();
}
