<fieldset>
	<legend><label for="overlayLanguageItemValue">{lang}wcf.acp.language.item.value{/lang}</label></legend>

	<dl class="wide">
		<dd>
			<textarea rows="5" cols="60" name="languageItemValue" id="overlayLanguageItemValue"{if $item->languageItemOriginIsSystem} readonly="readonly"{/if}>{$item->languageItemValue}</textarea>
		</dd>
	</dl>
</fieldset>

{if $item->languageItemOriginIsSystem}
	<fieldset>
		<legend><label for="overlayLanguageCustomItemValue">{lang}wcf.acp.language.item.customValue{/lang}</label></legend>
		
		<dl class="wide">
			<dd>
				<textarea rows="5" cols="60" name="languageCustomItemValue" id="overlayLanguageCustomItemValue">{$item->languageCustomItemValue}</textarea>
			</dd>
		</dl>
		
		<dl class="wide">
			<dd><label><input type="checkbox" name="languageUseCustomValue" id="overlayLanguageUseCustomValue" value="1" {if $item->languageUseCustomValue}checked="checked" {/if}/> {lang}wcf.acp.language.item.useCustomValue{/lang}</label></dd>
		</dl>
	</fieldset>
{/if}

<input type="hidden" name="languageItemID" id="overlayLanguageItemID" value="{@$item->languageItemID}" />

<div class="formSubmit">
	<button class="jsSubmitLanguageItem buttonPrimary" accesskey="s">{lang}wcf.global.button.submit{/lang}</button>
</div>