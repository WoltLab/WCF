{include file="documentHeader"}
<head>
	<title>{lang}wcf.global.error.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	<p class="error" id="errorMessage">
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

<!-- 
{$name} thrown in {$file} ({@$line})
Stracktrace:
{$stacktrace}
-->

{include file='footer' sandbox=false}
</body>
</html>