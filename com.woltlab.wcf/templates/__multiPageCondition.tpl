<dl>
	<dt></dt>
	<dd>
		<label><input type="checkbox" id="{$fieldName}_reverseLogic" name="{$fieldName}_reverseLogic" value="1"{if $reverseLogic} checked{/if}> {lang}wcf.page.requestedPage.condition.reverseLogic{/lang}</label>
		<small>{lang}wcf.page.requestedPage.condition.reverseLogic.description{/lang}</small>
	</dd>
</dl>

{@$conditionHtml}
