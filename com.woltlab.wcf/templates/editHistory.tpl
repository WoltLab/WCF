{include file='documentHeader'}

<head>
	<title>{$object->getTitle()} - {lang}wcf.edit.versions{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
</head>

<body id="tpl{$templateName|ucfirst}">

{include file='header'}

<header class="boxHeadline">
	<h1>{$object->getTitle()}</h1>
</header>

{include file='userNotice'}

<pre>{$diff}</pre>

{include file='footer'}

</body>
</html>
