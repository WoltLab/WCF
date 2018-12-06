{capture assign='pageTitle'}{lang}wcf.page.error.permissionDenied.title{/lang}{/capture}
{capture assign='contentTitle'}{lang}wcf.page.error.permissionDenied.title{/lang}{/capture}
{if !$isFirstVisit}
	{capture assign='contentHeaderNavigation'}
		<li id="backToReferrer" style="display: none"><a href="#" class="button" rel="noopener"><span class="icon icon16 fa-arrow-left"></span> {lang}wcf.page.error.backward{/lang}</a></li>
	{/capture}
	
	<script data-relocate="true">
		(function() {
			if (document.referrer) {
				var backToReferrer = elById('backToReferrer');
				elShow(backToReferrer);
				backToReferrer.children[0].href = document.referrer;
			}
		})();
	</script>
{/if}

{include file='header' __disableAds=true}

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.page.error.insufficientPermissions{/lang}</h2>
	
	<p id="errorMessage" class="fullPageErrorMessage" data-exception-class-name="{$exceptionClassName}">
		{if $message|isset}
			{@$message}
		{else}
			{lang}wcf.page.error.permissionDenied{/lang}
		{/if}
	</p>
</section>

{if !$__wcf->user->userID}
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.user.login{/lang}</h2>
		
		<p>{lang}wcf.page.error.loginAvailable{/lang}</p>
		<p style="margin-top: 20px"><a href="{link controller='Login' url=$__wcf->session->requestURI}{/link}" class="button"><span class="icon icon16 fa-key"></span> {lang}wcf.user.loginOrRegister{/lang}</a></p>
	</section>
{/if}

{event name='content'}

{if ENABLE_DEBUG_MODE}
	<!-- 
	{$name} thrown in {$file} ({@$line})
	Stacktrace:
	{$stacktrace}
	-->
{/if}

{include file='footer' __disableAds=true}
