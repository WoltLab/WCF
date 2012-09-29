{include file='header' templateName='userException'}

<p id="errorMessage" class="error">
	{@$message}
</p>

<script type="text/javascript">
	//<![CDATA[
	if (document.referrer) {
		$('#errorMessage').append('<br /><a href="' + document.referrer + '">{lang}wcf.global.error.backward{/lang}</a>'); 
	}
	//]]>
</script>

{if ENABLE_DEBUG_MODE}
	<!-- 
	{$name} thrown in {$file} ({@$line})
	Stracktrace:
	{$stacktrace}
	-->
{/if}
{include file='footer'}
