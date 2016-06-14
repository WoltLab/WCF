{include file='header' templateName='userException' templateNameApplication='wcf'}

<p id="errorMessage" class="error">
	{@$message}
</p>

<script data-relocate="true">
	//<![CDATA[
	if (document.referrer) {
		$('#errorMessage').append('<br><a href="' + document.referrer + '">{lang}wcf.global.error.backward{/lang}</a>');
	}
	//]]>
</script>

{if ENABLE_DEBUG_MODE}
	<!-- 
	{$name} thrown in {$file} ({@$line})
	Stacktrace:
	{$stacktrace}
	-->
{/if}
{include file='footer'}
