<label><input type="checkbox" id="search_{$option->optionName}" name="searchOptions[{$option->optionName}]"{if $searchOption} checked{/if}> {lang}wcf.user.option.searchTextOption{/lang}</label>
<textarea id="{$option->optionName}" name="values[{$option->optionName}]"{if !$searchOption} disabled{/if} cols="40" rows="10"{if $option->required} required{/if}>{$value}</textarea>

<script data-relocate="true">
	{
		const checkbox = document.getElementById('search_{unsafe:$option->optionName|encodeJS}');
		const textarea = document.getElementById('{unsafe:$option->optionName|encodeJS}');
		if (checkbox && textarea) {
			checkbox.addEventListener('change', () => {
				textarea.disabled = !checkbox.checked;
			});
		}
	}
</script>
