{capture assign='pageTitle'}{lang}wcf.page.redirect.title{/lang}{/capture}

{capture assign='headContent'}
	<meta http-equiv="refresh" content="{if $wait|isset}{@$wait}{else}10{/if};URL={$url}">
{/capture}

{include file='header' __disableAds=true}

<div class="{if !$status|empty}{@$status}{else}success{/if}">
	<p>{@$message}</p>
	<a href="{$url}">{lang}wcf.page.redirect.url{/lang}</a>
</div>

{include file='footer' __disableAds=true}
