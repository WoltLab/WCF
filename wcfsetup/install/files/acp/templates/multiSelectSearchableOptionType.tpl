<label><input type="checkbox" id="search_{$option->optionName}" name="searchOptions[{$option->optionName}]"{if $searchOption} checked{/if}> {lang}wcf.user.option.searchRadioButtonOption{/lang}</label>
<select id="{$option->optionName}" name="values[{$option->optionName}][]" multiple size="{if $selectOptions|count > 10}10{else}{$selectOptions|count}{/if}"{if !$searchOption} disabled{/if}>
	{foreach from=$selectOptions key=key item=selectOption}
		<option value="{$key}"{if $key|in_array:$value} selected{/if}>{lang}{unsafe:$selectOption}{/lang}</option>
	{/foreach}
</select>

<script data-relocate="true">
	{
		const checkbox = document.getElementById('search_{unsafe:$option->optionName|encodeJS}');
		const select = document.getElementById('{unsafe:$option->optionName|encodeJS}');
		if (checkbox && select) {
			checkbox.addEventListener('change', () => {
				select.disabled = !checkbox.checked;
			});
		}
	}
</script>
