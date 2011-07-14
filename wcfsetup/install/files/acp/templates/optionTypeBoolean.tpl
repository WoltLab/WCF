<input {if $disableOptions || $enableOptions}class="enablesOptions" data-disableOptions="[ {@$disableOptions}]" data-enableOptions="[ {@$enableOptions}]" {/if}
id="{$option->optionName}" type="checkbox" name="values[{$option->optionName}]" value="1"
{if $value} checked="checked"{/if} />