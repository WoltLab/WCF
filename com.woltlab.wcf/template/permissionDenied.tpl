{include file="documentHeader"}

<head>
	<title>{lang}wcf.global.error.permissionDenied.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	
	{include file='headInclude'}
</head>

<body id="tpl{$templateName|ucfirst}">

{include file='header'}

<p class="error">{lang}wcf.global.error.permissionDenied{/lang}</p>

{event name='content'}

{if ENABLE_DEBUG_MODE}
	<!-- 
	{$name} thrown in {$file} ({@$line})
	Stracktrace:
	{$stacktrace}
	-->
{/if}

{include file='footer'}

</body>
</html>