{if $isMultiDomainSetup}
	<select name="values[{$option->optionName}]" id="{$option->optionName}">
		{foreach from=$applications item=application}
			<option value="{@$application->packageID}"{if $application->packageID == $value} selected{/if}>{$application->getPackage()}</option>
		{/foreach}
	</select>
{else}
	{* TODO: hide *}
	<input type="hidden" name="values[{$option->optionName}]" value="1">
	<script>
		(function() {
			{* pretend that this option does not exist *}
			var container = elBySel('.{$option->optionName}Input');
			container.style.setProperty('margin', '0', 'important');
			container.style.setProperty('max-height', '0', 'important');
			container.style.setProperty('overflow', 'hidden', 'important');
		})();
	</script>
{/if}
