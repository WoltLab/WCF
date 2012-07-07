{include file='header' templateName='permissionDenied'}

<p class="error">{lang}wcf.global.error.permissionDenied{/lang}</p>

{if ENABLE_DEBUG_MODE}
	<!-- 
	{$name} thrown in {$file} ({@$line})
	Stracktrace:
	{$stacktrace}
	-->
{/if}

{include file='footer'}
