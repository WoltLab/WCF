{include file='header' templateName='permissionDenied' templateNameApplication='wcf'}

<woltlab-core-notice type="error">{lang}wcf.page.error.permissionDenied{/lang}</woltlab-core-notice>

{if ENABLE_DEBUG_MODE}
	<!-- 
	{$name} thrown in {$file} ({@$line})
	Stacktrace:
	{$stacktrace}
	-->
{/if}

{include file='footer'}
