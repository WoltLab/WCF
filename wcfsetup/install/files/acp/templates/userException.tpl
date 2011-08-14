{include file='header' templateName='userException'}

<p id="errorMessage" class="error">
	{@$message}
</p>

<script type="text/javascript">
	//<![CDATA[
	if (document.referrer) {
		onloadEvents.push(function() { document.getElementById('errorMessage').innerHTML += "<br /><a href=\"" + document.referrer + "{@SID_ARG_2ND_NOT_ENCODED}\">{lang}wcf.global.error.backward{/lang}</a>"; });
	}
	//]]>
</script>

<!-- 
{$name} thrown in {$file} ({@$line})
Stracktrace:
{$stacktrace}
-->

{include file='footer'}
