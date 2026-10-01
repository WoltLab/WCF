{capture assign='pageTitle'}{lang}wcf.page.error.permissionDenied.title{/lang}{/capture}
{capture assign='contentTitle'}{lang}wcf.page.error.permissionDenied.title{/lang}{/capture}
{capture assign='contentHeaderNavigation'}
	<li id="backToReferrer" style="display: none"><a href="#" class="button" rel="noopener">{icon name='arrow-left'} {lang}wcf.page.error.backward{/lang}</a></li>
{/capture}

<script data-relocate="true">
	(function() {
		// Visitors arriving from another site, e.g. a search engine, must not be sent back there.
		if (document.referrer && new URL(document.referrer).origin === window.location.origin) {
			var backToReferrer = elById('backToReferrer');
			elShow(backToReferrer);
			backToReferrer.children[0].href = document.referrer;
		}
	})();
</script>

{include file='header' __disableAds=true}

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.page.error.insufficientPermissions{/lang}</h2>
	
	<p id="errorMessage" class="fullPageErrorMessage" data-exception-class-name="{$exceptionClassName}">
		{if $message|isset}
			{unsafe:$message}
		{else}
			{lang}wcf.page.error.permissionDenied{/lang}
		{/if}
	</p>
</section>

{if $__wcf->user->isGuest()}
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.user.login{/lang}</h2>
		
		<p>{lang}wcf.page.error.loginAvailable{/lang}</p>
		<p style="margin-top: 20px">
			<a
				href="{link controller='Login' url=$__wcf->getRequestURI()}{/link}"
				class="button"
				rel="nofollow"
			>{icon name='key'} {lang}wcf.user.button.login{/lang}</a>
		</p>
	</section>
{/if}

{event name='content'}

{if ENABLE_DEBUG_MODE}
	<!-- 
	{$name} thrown in {$file} ({$line})
	Stacktrace:
	{$stacktrace}
	-->
{/if}

{include file='footer' __disableAds=true}
