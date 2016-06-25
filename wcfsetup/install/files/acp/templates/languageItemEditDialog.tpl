<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.language.item.value{/lang}</h2>
	
	<dl class="wide">
		<dt></dt>
		<dd>
			<textarea rows="5" cols="60" name="languageItemValue" id="overlayLanguageItemValue"{if $item->languageItemOriginIsSystem} readonly{/if}>{$item->languageItemValue}</textarea>
		</dd>
	</dl>
</section>

{if $item->languageItemOriginIsSystem}
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.language.item.customValue{/lang}</h2>
		
		<dl class="wide">
			<dt></dt>
			<dd>
				<textarea rows="5" cols="60" name="languageCustomItemValue" id="overlayLanguageCustomItemValue">{$item->languageCustomItemValue}</textarea>
			</dd>
		</dl>
		
		<dl class="wide">
			<dt></dt>
			<dd><label><input type="checkbox" name="languageUseCustomValue" id="overlayLanguageUseCustomValue" value="1"{if $item->languageUseCustomValue} checked{/if}> {lang}wcf.acp.language.item.useCustomValue{/lang}</label></dd>
		</dl>
	</section>
{/if}

<input type="hidden" name="languageItemID" id="overlayLanguageItemID" value="{@$item->languageItemID}">

<div class="formSubmit">
	<button class="jsSubmitLanguageItem buttonPrimary" accesskey="s">{lang}wcf.global.button.submit{/lang}</button>
</div>