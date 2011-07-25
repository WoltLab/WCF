<input {if $optionData.enableOptions}onclick="{@$optionData.enableOptions}" {/if}
id="{$optionData.optionName}" type="checkbox" name="values[{$optionData.optionName}]" value="1"
{if $optionData.optionValue}checked="checked" {/if}/>