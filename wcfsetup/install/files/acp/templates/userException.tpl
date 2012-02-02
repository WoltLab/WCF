{include file='header' templateName='userException'}

<p id="errorMessage" class="wcf-error">
	{@$message}
</p>

<script type="text/javascript">
	//<![CDATA[
	if (document.referrer) {
		$('#errorMessage').append('<br /><a href="' + document.referrer + '">{lang}wcf.global.error.backward{/lang}</a>'); 
	}
	//]]>
</script>

<!-- 
{$name} thrown in {$file} ({@$line})
Stracktrace:
{$stacktrace}
-->

{include file='footer'}
