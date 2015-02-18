{include file="documentHeader"}
<head>
	<title>{lang}wcf.global.error.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	
	{include file='headInclude'}
</head>

<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>

{include file='header' __disableAds=true}
	
<p id="errorMessage" class="error">
	{@$message}
</p>
<script data-relocate="true">
	//<![CDATA[
	if (document.referrer) {
		$('#errorMessage').append('<br /><a href="' + document.referrer + '">{lang}wcf.global.error.backward{/lang}</a>'); 
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

</body>
</html>
