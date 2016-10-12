<input type="file" id="{$option->optionName}" name="{$option->optionName}" value="">
{if $value}
	<label><input type="checkbox" id="{$option->optionName}_remove_file" name="values[{$option->optionName}]"> {lang}wcf.acp.option.{$option->optionName}.removeFile{/lang}</label>
{/if}
