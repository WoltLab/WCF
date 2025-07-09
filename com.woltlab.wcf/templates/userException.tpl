{if !$title|empty}
	{capture assign='pageTitle'}{$title}{/capture}
	{capture assign='contentTitle'}{$title}{/capture}
{else}
	{capture assign='pageTitle'}{lang}wcf.global.error.title{/lang}{/capture}
	{capture assign='contentTitle'}{lang}wcf.global.error.title{/lang}{/capture}
{/if}

{include file='header' __disableAds=true}

<div class="section">
	<div class="box64 userException">
		{icon size=64 name='circle-exclamation'}
		<p id="errorMessage" class="fullPageErrorMessage userExceptionMessage" data-exception-class-name="{$exceptionClassName}">
			{unsafe:$message}
		</p>
	</div>
</div>

<script data-relocate="true">
	if (document.referrer) {
		document.getElementById('errorMessage').insertAdjacentHTML('beforeend', `<br><br><a href="${ document.referrer }">{lang}wcf.page.error.backward{/lang}</a>`);
	}
</script>

{if ENABLE_DEBUG_MODE}
	<!-- 
	{$name} thrown in {$file} ({$line})
	Stacktrace:
	{$stacktrace}
	-->
	<script>
		console.debug('{unsafe:$name|encodeJS} thrown in {unsafe:$file|encodeJS} ({$line})');
		console.debug('Stacktrace:\n{unsafe:$stacktrace|encodeJS}');
	</script>
{/if}

{include file='footer' __disableAds=true}
