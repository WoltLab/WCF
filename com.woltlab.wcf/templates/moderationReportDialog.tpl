{if $alreadyReported}
	<p>{lang}wcf.moderation.report.alreadyReported{/lang}</p>
{else}
	<dl>
		<dt><label for="reason">{lang}wcf.moderation.report.reason{/lang}</label></dt>
		<dd>
			<textarea id="reason" required cols="60" rows="10" class="jsReportMessage" maxlength="64000"></textarea>
			<small>{lang}wcf.moderation.report.reason.description{/lang}</small>
		</dd>
	</dl>

	{event name='reasonFields'}
{/if}