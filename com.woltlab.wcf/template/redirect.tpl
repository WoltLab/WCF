{include file="documentHeader"}

<head>
	<title>{lang}wcf.global.redirect.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	
	{include file='headInclude'}
	
	<meta http-equiv="refresh" content="{if $wait|isset}{@$wait}{else}10{/if};URL={$url}" />
</head>

<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>

{include file='header'}
	
<div class="{if !$status|empty}{@$status}{else}success{/if}">
	<p>{@$message}</p>
	<a href="{$url}">{lang}wcf.global.redirect.url{/lang}</a>
</div>
	
{include file='footer'}

</body>
</html>