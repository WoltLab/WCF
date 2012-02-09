{include file="documentHeader"}
<head>
	<title>{lang}wcf.global.error.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>

<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main" class="wcf-main">
	<p id="errorMessage" class="wcf-error">
		{@$message}
	</p>
</div>
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

{include file='footer' sandbox=false}
</body>
</html>
