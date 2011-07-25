{include file="documentHeader"}
<head>
	<title>{lang}wcf.global.error.permissionDenied.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	<p class="error">{lang}wcf.global.error.permissionDenied{/lang}</p>

</div>

{include file='footer' sandbox=false}
</body>
</html>