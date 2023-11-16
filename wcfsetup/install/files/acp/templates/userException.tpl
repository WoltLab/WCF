{include file='header' pageTitle='wcf.global.error.title' templateName='userException' templateNameApplication='wcf'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.global.error.title{/lang}</h1>
	</div>
</header>

<div class="section">
	<div class="box64 userException">
		{icon size=64 name='circle-exclamation'}
		<p id="errorMessage" class="fullPageErrorMessage userExceptionMessage" data-exception-class-name="{$exceptionClassName}">
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
	<script>
		console.debug('{$name|encodeJS} thrown in {$file|encodeJS} ({@$line})');
		console.debug('Stacktrace:\n{@$stacktrace|encodeJS}');
	</script>
{/if}

{include file='footer'}
