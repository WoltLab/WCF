{include file="shared_multipleSelectionFormField"}

{if $field->getVisibleEverywhereFieldId() !== null}
	<script data-relocate="true">
		{
			const label = document.querySelector('label[for="{unsafe:$field->getPrefixedId()|encodeJS}"]');

			document.querySelectorAll('input[name="{unsafe:$field->getVisibleEverywhereFieldId()|encodeJS}"]').forEach((input) => {
				input.addEventListener("change", () => {
					setLabelText(input.value);
				});
			});

			function setLabelText (value) {
				label.innerHTML = parseInt(value) === 0 ? '{unsafe:$field->getLabel()|encodeJS}' : '{unsafe:$field->getInvertedLabel()|encodeJS}';
			}

			setLabelText(document.querySelector('input[name="{unsafe:$field->getVisibleEverywhereFieldId()|encodeJS}"]:checked').value);
		}
	</script>
{/if}
