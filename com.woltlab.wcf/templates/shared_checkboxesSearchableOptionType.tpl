<label><input type="checkbox" id="search_{$option->optionName}" name="searchOptions[{$option->optionName}]"{if $searchOption} checked{/if}> {lang}wcf.user.option.searchRadioButtonOption{/lang}</label>
{foreach from=$selectOptions key=key item=selectOption}
	<label><input type="checkbox" name="values[{$option->optionName}][]" value="{$key}" {if $key|in_array:$value} checked{/if}{if !$searchOption} disabled{/if}> {lang}{$selectOption}{/lang}</label>
{/foreach}

<script data-relocate="true">
	{
		const checkbox = document.getElementById('search_{unsafe:$option->optionName|encodeJS}');
		const inputs = document.querySelectorAll('input[name="values[{unsafe:$option->optionName|encodeJS}][]"]');
		if (checkbox) {
			checkbox.addEventListener('change', () => {
				inputs.forEach((input) => {
					input.disabled = !checkbox.checked;
				});
			});
		}
	}
</script>
