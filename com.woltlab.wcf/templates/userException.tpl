{if !$title|empty}
	{capture assign='pageTitle'}{$title}{/capture}
	{capture assign='contentTitle'}{$title}{/capture}
{else}
	{capture assign='pageTitle'}{lang}wcf.global.error.title{/lang}{/capture}
	{capture assign='contentTitle'}{lang}wcf.global.error.title{/lang}{/capture}
{/if}

{include file='header' __disableAds=true}

<div class="section">
	<p id="errorMessage">
		{@$message}
	</p>
</div>

<script data-relocate="true">
	//<![CDATA[
	if (document.referrer) {
		$('#errorMessage').append('<br><br><a href="' + document.referrer + '">{lang}wcf.page.error.backward{/lang}</a>'); 
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

{include file='footer' __disableAds=true}
