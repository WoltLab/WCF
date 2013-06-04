{include file='documentHeader'}

<head>
	<title>{lang}wcf.global.error.title{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
</head>

<body id="tpl{$templateName|ucfirst}">

{include file='header'}

<div class="warning">
	<p><strong>{lang}wcf.page.offline{/lang}</strong></p>
	<p>{if OFFLINE_MESSAGE_ALLOW_HTML}{@OFFLINE_MESSAGE}{else}{@OFFLINE_MESSAGE|htmlspecialchars|nl2br}{/if}</p>
</div>

{include file='footer'}

</body>
</html>