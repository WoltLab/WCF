{if $alreadyReported}
	<p class="info">{lang}wcf.moderation.report.alreadyReported{/lang}</p>
{else}
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.moderation.report.reason{/lang}</h2>
		
		<dl class="wide">
			<dd>
				<textarea id="reason" required cols="60" rows="10" class="jsReportMessage" maxlength="64000"></textarea>
				<small>{lang}wcf.moderation.report.reason.description{/lang}</small>
			</dd>
		</dl>
		
		{event name='reasonFields'}
	</section>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<button class="jsSubmitReport buttonPrimary" accesskey="s">{lang}wcf.global.button.submit{/lang}</button>
	</div>
{/if}