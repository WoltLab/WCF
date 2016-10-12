<dl{if $errorField == $reasonFieldName} class="formError"{/if}>
	<dt><label for="{$reasonFieldName}">{lang}wcf.global.reason{/lang}</label></dt>
	<dd>
		<textarea name="{$reasonFieldName}" id="{$reasonFieldName}" cols="40" rows="3">{$reason}</textarea>
		
		{if $errorField == $reasonFieldName}
			<small class="innerError">{lang}wcf.global.form.error.{$errorType}{/lang}</small>
		{/if}
	</dd>
</dl>
