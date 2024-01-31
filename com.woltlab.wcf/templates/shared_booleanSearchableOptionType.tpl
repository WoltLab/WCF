<label><input type="checkbox" id="search_{$option->optionName}" name="searchOptions[{$option->optionName}]"{if $searchOption} checked{/if}> {lang}wcf.user.option.searchBooleanOption{/lang}</label>
<ol class="flexibleButtonGroup optionTypeBoolean">
	<li>
		<input type="radio" id="{$option->optionName}"{if $value == 1} checked{/if} name="values[{$option->optionName}]" value="1"{if $disableOptions || $enableOptions} class="jsEnablesOptions" data-is-boolean="true" data-disable-options="[ {@$disableOptions}]" data-enable-options="[ {@$enableOptions}]"{/if}{if !$searchOption} disabled{/if}>
		<label for="{$option->optionName}" class="green">{icon name='check'} {lang}wcf.acp.option.type.boolean.yes{/lang}</label>
	</li>
	<li>
		<input type="radio" id="{$option->optionName}_no"{if $value == 0} checked{/if} name="values[{$option->optionName}]" value="0"{if $disableOptions || $enableOptions} class="jsEnablesOptions" data-is-boolean="true" data-disable-options="[ {@$disableOptions}]" data-enable-options="[ {@$enableOptions}]"{/if}{if !$searchOption} disabled{/if}>
		<label for="{$option->optionName}_no" class="red">{icon name='xmark'} {lang}wcf.acp.option.type.boolean.no{/lang}</label>
	</li>
</ol>

<script data-relocate="true">
	$(function() {
		$('#search_{$option->optionName}').change(function(event) {
			if ($(event.currentTarget).prop('checked')) {
				$('#{$option->optionName}').enable();
				$('#{$option->optionName}_no').enable();
			}
			else {
				$('#{$option->optionName}').disable();
				$('#{$option->optionName}_no').disable();
			}
		});
	});
</script>
