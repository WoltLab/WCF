{include file='header' templateName='permissionDenied' templateNameApplication='wcf'}

<p class="error">{lang}wcf.page.error.permissionDenied{/lang}</p>

{if ENABLE_DEBUG_MODE}
	<!-- 
	{$name} thrown in {$file} ({@$line})
	Stacktrace:
	{$stacktrace}
	-->
{/if}

{include file='footer'}
