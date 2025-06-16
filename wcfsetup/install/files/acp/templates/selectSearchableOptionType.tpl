<label><input type="checkbox" id="search_{$option->optionName}" name="searchOptions[{$option->optionName}]"{if $searchOption} checked{/if}> {lang}wcf.user.option.searchRadioButtonOption{/lang}</label>
<select id="{$option->optionName}" name="values[{$option->optionName}]"{if !$searchOption} disabled{/if}>
	{if !$allowEmptyValue|empty}<option value="">{lang}wcf.global.noSelection{/lang}</option>{/if}
	{foreach from=$selectOptions key=key item=selectOption}
		<option value="{$key}"{if $value == $key} selected{/if}>{lang}{$selectOption}{/lang}</option>
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
