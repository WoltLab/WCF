type CategoryId = number;
type LabelGroupId = number;

function toggleVisibility(showLabelGroupIds: LabelGroupId[] | undefined): void {
  if (showLabelGroupIds === undefined) {
    showLabelGroupIds = [];
  }

  // TODO: Missing typings for `<woltlab-core-label-picker>`
  document.querySelectorAll<HTMLElement>("woltlab-core-label-picker").forEach((labelPicker) => {
    const groupId = parseInt(labelPicker.dataset.groupId!);
    if (showLabelGroupIds!.includes(groupId)) {
      (labelPicker as any).disabled = false;
      labelPicker.closest("dl")!.hidden = false;
    } else {
      (labelPicker as any).disabled = true;
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
