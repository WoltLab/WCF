{include file="documentHeader"}

<head>
	<title>{lang}wcf.global.redirect.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	
	{include file='headInclude'}
	
	<meta http-equiv="refresh" content="{if $wait|isset}{@$wait}{else}10{/if};URL={$url}" />
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{include file='header' __disableAds=true}

<div class="{if !$status|empty}{@$status}{else}success{/if}">
	<p>{@$message}</p>
	<a href="{$url}">{lang}wcf.global.redirect.url{/lang}</a>
</div>

{include file='footer' __disableAds=true}

</body>
</html>
