<label><input type="checkbox" id="search_{$option->optionName}" name="searchOptions[{$option->optionName}]"{if $searchOption} checked{/if}> {lang}wcf.user.option.searchTextOption{/lang}</label>
<input type="{$inputType}" id="{$option->optionName}" name="values[{$option->optionName}]" value="{$value}"{if $inputClass} class="{$inputClass}"{/if}{if !$searchOption} disabled{/if}{if $option->required} required{/if}>

<script data-relocate="true">
	{
		const checkbox = document.getElementById('search_{unsafe:$option->optionName|encodeJS}');
		const inputField = document.getElementById('{unsafe:$option->optionName|encodeJS}');
		{if $inputType === 'date'}
		const datePicker = document.getElementById('{unsafe:$option->optionName|encodeJS}DatePicker');
		{/if}

		if (checkbox) {
			checkbox.addEventListener('change', () => {
				if (checkbox.checked) {
					inputField.disabled = false;

					{if $inputType === 'date'}
					datePicker.disabled = false;
					{/if}
				} else {
					inputField.disabled = true;

					{if $inputType === 'date'}
					datePicker.disabled = true;
					{/if}
				}
			});
		}

		{if !$searchOption && $inputType === 'date'}
		datePicker.disabled = true;
		{/if}
	}
</script>
