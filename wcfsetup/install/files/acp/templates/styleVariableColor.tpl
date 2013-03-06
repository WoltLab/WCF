<figure>
	<figcaption>{lang}wcf.acp.style.colors.{$languageVariable}{/lang}</figcaption>
	<div class="colorPreview"><div class="jsColorPicker jsTooltip" title="{$variableName}" style="background-color: {$variables[$variableName]}" data-color="{$variables[$variableName]}" data-store="{$variableName}_value"></div></div>
	<input type="hidden" id="{$variableName}_value" name="{$variableName}" value="{$variables[$variableName]}" /> 
</figure>