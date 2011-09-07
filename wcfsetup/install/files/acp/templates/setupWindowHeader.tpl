<!DOCTYPE html>
<html dir="{@$__wcf->getLanguage()->getPageDirection()}" lang="{@$__wcf->getLanguage()->getFixedLanguageCode()}">
<head>
	<meta charset="utf-8" />
	<title></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<link href="{@RELATIVE_WCF_DIR}acp/style/extra/setupWindowStyle{if $__wcf->getLanguage()->getPageDirection() == 'rtl'}-rtl{/if}.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
		//<![CDATA[
		function changeHeight() {
			if (parent.document.getElementById('iframe').style.visibility != 'hidden') {
				parent.document.getElementById('iframe').style.height = document.getElementById('content').offsetHeight + 4 + 'px';
			}
		}
		//]]>
	</script>
</head>

<body>
	<div id="content" class="page">
		