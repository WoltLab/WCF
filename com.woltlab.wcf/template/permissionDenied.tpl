{include file="documentHeader"}

<head>
	<title>{lang}wcf.global.error.permissionDenied.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>

<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}
	
	<p class="wcf-error">{lang}wcf.global.error.permissionDenied{/lang}</p>
	
{if ENABLE_DEBUG_MODE}
	<!-- 
	{$name} thrown in {$file} ({@$line})
	Stracktrace:
	{$stacktrace}
	-->
{/if}

{include file='footer' sandbox=false}
</body>
</html>