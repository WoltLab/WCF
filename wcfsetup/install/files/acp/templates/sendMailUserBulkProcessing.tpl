<dl{if $errorField == 'subject'} class="formError"{/if}>
	<dt><label for="subject">{lang}wcf.acp.user.sendMail.subject{/lang}</label></dt>
	<dd>
		<input type="text" id="subject" name="subject" value="{$subject}" class="long">
		{if $errorField == 'subject'}
			<small class="innerError">
				{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
			</small>
		{/if}
	</dd>
</dl>

<dl{if $errorField == 'from'} class="formError"{/if}>
	<dt><label for="from">{lang}wcf.acp.user.sendMail.from{/lang}</label></dt>
	<dd>
		<input type="text" id="from" name="from" value="{$from}" class="medium">
		{if $errorField == 'from'}
			<small class="innerError">
				{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
			</small>
		{/if}
		{* TODO: Add field for Human readable name for new mail system *}
		<small>{lang}wcf.acp.user.sendMail.from.description{/lang}</small>
	</dd>
</dl>

<dl{if $errorField == 'text'} class="formError"{/if}>
	<dt><label for="text">{lang}wcf.acp.user.sendMail.text{/lang}</label></dt>
	<dd>
		<textarea id="text" name="text" rows="15" cols="40">{$text}</textarea>
		{if $errorField == 'text'}
			<small class="innerError" class="long">
				{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
			</small>
		{/if}
	</dd>
</dl>

<dl>
	<dt></dt>
	<dd>
		<label for="enableHTML"><input type="checkbox" id="enableHTML" name="enableHTML" value="1"{if $enableHTML == 1} checked{/if}> {lang}wcf.acp.user.sendMail.enableHTML{/lang}</label>
	</dd>
</dl>

{if !$mailID|empty}
	<script data-relocate="true">
		require(['Language'], function(Language) {
			Language.add('wcf.acp.worker.abort.confirmMessage', '{lang}wcf.acp.worker.abort.confirmMessage{/lang}');
			
			new WCF.ACP.Worker('mail', 'wcf\\system\\worker\\MailWorker', '', {
				mailID: {@$mailID}
			});
		});
	</script>
{/if}
