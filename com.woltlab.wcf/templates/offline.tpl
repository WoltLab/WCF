{include file='documentHeader'}

<head>
	<title>{PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
</head>

<body id="tpl{$templateName|ucfirst}">

{include file='header' skipBreadcrumbs=true}

<div class="warning">
	<p><strong>{lang}wcf.page.offline{/lang}</strong></p>
	<p>{if OFFLINE_MESSAGE_ALLOW_HTML}{@OFFLINE_MESSAGE|language}{else}{@OFFLINE_MESSAGE|language|newlineToBreak}{/if}</p>
</div>

{include file='footer'}

</body>
</html>