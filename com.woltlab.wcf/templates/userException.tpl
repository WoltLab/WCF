{if !$title|empty}
	{capture assign='pageTitle'}{$title}{/capture}
	{capture assign='contentTitle'}{$title}{/capture}
{else}
	{capture assign='pageTitle'}{lang}wcf.global.error.title{/lang}{/capture}
	{capture assign='contentTitle'}{lang}wcf.global.error.title{/lang}{/capture}
{/if}

{include file='header' __disableAds=true}

<div class="section">
	<div class="box64">
		<span class="icon icon64 fa-exclamation-circle"></span>
		<p id="errorMessage" class="fullPageErrorMessage" data-exception-class-name="{$exceptionClassName}">
			{@$message}
		</p>
	</div>
</div>

<script data-relocate="true">
	if (document.referrer) {
		$('#errorMessage').append('<br><br><a href="' + document.referrer + '">{lang}wcf.page.error.backward{/lang}</a>');
	}
</script>

{if ENABLE_DEBUG_MODE}
	<!-- 
	{$name} thrown in {$file} ({@$line})
	Stacktrace:
	{$stacktrace}
	-->
{/if}

{include file='footer' __disableAds=true}
