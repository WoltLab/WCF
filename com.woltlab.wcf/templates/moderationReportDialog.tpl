{if $alreadyReported}
	<p class="info">{lang}wcf.moderation.report.alreadyReported{/lang}</p>
{else}
	<fieldset>
		<legend><label for="reason">{lang}wcf.moderation.report.reason{/lang}</label></legend>
		
		<dl class="wide">
			<dd>
				<textarea id="reason" required="required" cols="60" rows="10" class="jsReportMessage" maxlength="64000"></textarea>
				<small>{lang}wcf.moderation.report.reason.description{/lang}</small>
			</dd>
		</dl>
		
		{event name='reasonFields'}
	</fieldset>
	
	{event name='fieldsets'}
	
	<div class="formSubmit">
		<button class="jsSubmitReport buttonPrimary" accesskey="s">{lang}wcf.global.button.submit{/lang}</button>
	</div>
{/if}