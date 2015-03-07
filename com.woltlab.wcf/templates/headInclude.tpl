<base href="{$baseHref}" />
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="format-detection" content="telephone=no" />
{if $allowSpidersToIndexThisPage|empty}<meta name="robots" content="noindex,nofollow" />{/if}
{implode from=$__wcf->getMetaTagHandler() item=__metaTag glue="\n"}{@$__metaTag}{/implode}
{event name='metaTags'}

<!-- Stylesheets -->
{@$__wcf->getStyleHandler()->getStylesheet()}
{event name='stylesheets'}

{include file='headIncludeJavaScript'}

<script data-relocate="true">
	WCF.Language.addObject({
		{* dummy language item to preserve compatibility with WCF 2.0, move this to headIncludeJavaScript *}
		'wcf.global.error.title': '{lang}wcf.global.error.title{/lang}'
		
		{* DEPRECATED -- PLEASE USE javascriptLanguageImport@headIncludeJavaScript *}
		{event name='javascriptLanguageImport'}
	});
</script>

{* DEPRECATED -- PLEASE USE javascriptInclude@headIncludeJavaScript *}
{event name='javascriptInclude'}

<!-- Icons -->
<link rel="icon" href="{@$__wcf->getFavicon()}" type="image/x-icon" />
<link rel="apple-touch-icon" href="{@$__wcf->getPath()}images/apple-touch-icon.png" />

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		{* DEPRECATED -- PLEASE USE javascriptInit@headIncludeJavaScript *}
		{event name='javascriptInit'}
	});
	//]]>
</script>
