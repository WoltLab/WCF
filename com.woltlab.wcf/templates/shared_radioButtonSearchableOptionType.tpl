<label><input type="checkbox" id="search_{$option->optionName}" name="searchOptions[{$option->optionName}]"{if $searchOption} checked{/if}> {lang}wcf.user.option.searchRadioButtonOption{/lang}</label>
{foreach from=$selectOptions key=key item=selectOption}
	<label><input type="radio" name="values[{$option->optionName}]" value="{$key}" {if $value == $key} checked{/if} {if $disableOptions[$key]|isset || $enableOptions[$key]|isset}class="jsEnablesOptions" data-disable-options="[ {$disableOptions[$key]}]" data-enable-options="[ {$enableOptions[$key]}]"{/if}{if !$searchOption} disabled{/if}> {lang}{$selectOption}{/lang}</label>
{/foreach}

<script data-relocate="true">
	{
		const checkbox = document.getElementById('search_{unsafe:$option->optionName|encodeJS}');
		const radioInputs = document.querySelectorAll('input[name="values[{unsafe:$option->optionName|encodeJS}]"]');
		if (checkbox) {
			checkbox.addEventListener('change', () => {
				radioInputs.forEach((input) => {
					input.disabled = !checkbox.checked;
				});
			});
		}
	}
</script>
