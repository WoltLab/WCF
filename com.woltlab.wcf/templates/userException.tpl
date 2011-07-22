{include file="documentHeader"}
<head>
	<title>{lang}wcf.global.error.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
	<script type="text/javascript">
		//<![CDATA[
		if (document.referrer) {
			onloadEvents.push(function() { document.getElementById('errorMessage').innerHTML += "<br /><a href=\"" + document.referrer + "\">{lang}wcf.global.error.backward{/lang}</a>"; });
		}
		//]]>
	</script>
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	<p class="error" id="errorMessage">
		{@$message}
	</p>
</div>

<!-- 
{$name} thrown in {$file} ({@$line})
Stracktrace:
{$stacktrace}
-->

{include file='footer' sandbox=false}
</body>
</html>