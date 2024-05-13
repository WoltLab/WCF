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
		require(['Language', 'WoltLabSuite/Core/Acp/Ui/Worker'], function (Language, AcpUiWorker) {
			Language.add('wcf.acp.worker.abort.confirmMessage', '{jslang}wcf.acp.worker.abort.confirmMessage{/jslang}');
			
			new AcpUiWorker({
				dialogId: 'mail',
				dialogTitle: '{jslang}wcf.acp.user.bulkProcessing.sendMail{/jslang}',
				className: 'wcf\\system\\worker\\MailWorker',
				parameters: {
					mailID: {@$mailID},
				},
			});
		});
	</script>
{/if}
