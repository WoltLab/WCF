{if $title !== ''}
	{capture assign='pageTitle'}{$title}{/capture}
	{capture assign='contentTitle'}{$title}{/capture}
{else}
	{capture assign='pageTitle'}{lang}wcf.global.error.title{/lang}{/capture}
	{capture assign='contentTitle'}{lang}wcf.global.error.title{/lang}{/capture}
{/if}

{include file='header' __disableAds=true}

{if ENABLE_DEBUG_MODE}
{if $exception !== null}
<!--
{* A comment may not contain double dashes. *}
{@'--'|str_replace:'- -':$exception}
-->
{/if}
{/if}

<div class="section">
	<div class="box64 userException">
		{icon size=64 name='circle-exclamation'}
		<p>
			{@$message}
		</p>
	</div>
</div>

{if $showLogin}
<section class="section">
	<h2 class="sectionTitle">{lang}wcf.user.login{/lang}</h2>
	
	<p>{lang}wcf.page.error.loginAvailable{/lang}</p>
	<p style="margin-top: 20px">
		<a
			href="{link controller='Login' url=$__wcf->getRequestURI()}{/link}"
			class="button"
			rel="nofollow"
		>{icon name='key'} {lang}wcf.user.loginOrRegister{/lang}</a>
	</p>
</section>
{/if}

{include file='footer' __disableAds=true}
