<script data-relocate="true">
	require(["WoltLabSuite/Core/Component/CodeMirror"], ({ setupCodeMirror }) => {
		document.querySelectorAll("{unsafe:$codemirrorSelector|encodeJS}").forEach((textarea) => {
			setupCodeMirror(textarea, {
				{if $codemirrorMode|isset}
					language: "{unsafe:$codemirrorMode|encodeJS}",
				{/if}
				readOnly: {if !$editable|isset || $editable}false{else}true{/if},
			});
		});
	});
</script>
