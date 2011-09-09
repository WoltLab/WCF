<!DOCTYPE html>
<html dir="{@$__wcf->getLanguage()->getPageDirection()}" lang="{@$__wcf->getLanguage()->getFixedLanguageCode()}">
<head>
	<meta charset="utf-8" />
	<title>{lang}wcf.global.progressBar{/lang} - {lang}wcf.global.pageTitle{/lang}</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	
	<!-- Stylesheets -->
	<style type="text/css">
		/*<![CDATA[*/
		body {
			font-family: "Trebuchet MS", Tahoma, Verdana, Arial, Helvetica, sans-serif; 
			color: #333;
			font-size: .82em;
			margin: 0;
			padding: 0;
			background-color: #eee;
			background-image: url({if 'RELATIVE_WCF_DIR'|defined}{@' '|str_replace:'%20':RELATIVE_WCF_DIR}acp/images/setupBackground-{@$__wcf->getLanguage()->getPageDirection()}.png{else}install.php?showImage=setupBackground-{@$__wcf->getLanguage()->getPageDirection()}.png&tmpFilePrefix={@$tmpFilePrefix}{/if});
			background-repeat: repeat-y;
			background-position: {if $__wcf->getLanguage()->getPageDirection() == 'ltr'}left{else}right{/if};
		}
		
		.page {
			background-image: url({if 'RELATIVE_WCF_DIR'|defined}{@' '|str_replace:'%20':RELATIVE_WCF_DIR}acp/images/setupHeader-{@$__wcf->getLanguage()->getPageDirection()}.jpg{else}install.php?showImage=setupHeader-{@$__wcf->getLanguage()->getPageDirection()}.jpg&tmpFilePrefix={@$tmpFilePrefix}{/if});
			background-repeat: no-repeat;
			background-color: #fff;
			padding: 153px 40px 20px 40px;
			width: 720px;
		}
		
		h1 {
			color: #164369;
			text-shadow: 0 2px 3px #bbb;
			font-size: 1.9em;
			font-weight: normal;
			margin: 5px 0;
			padding: 5px 0;
		}
		
		h2 {
			color: #164369;
			font-size: 1.4em;
			font-weight: bold;
			margin: 0;
			padding-top: 5px;
		}
		
		.info, .success, .warning, .error, .help {
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
		}
		
		fieldset {
			font-size: .82em;
			border: 1px solid #8da4b7;
			margin-bottom: 10px;
			padding: 0;
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
		}
		
		legend {
			color: #487397;
			font-size: 1em;
			margin: 0 10px;
			padding: 0 4px;
		}
		
		fieldset p, fieldset div {
			margin: 0;
			padding: 0 0 5px 0;
		}
					
		fieldset ul {
			list-style: none;
			margin: 0;
			padding: 0;
		}
			
		fieldset ul li {
			padding-{if $__wcf->getLanguage()->getPageDirection() == 'ltr'}right{else}left{/if}: 3%;
			float: {if $__wcf->getLanguage()->getPageDirection() == 'ltr'}left{else}right{/if};
			width: 30%;
		}
		
		form {
			padding: 0;
			margin: 0;
		}
		
		textarea, select, input[type="text"], input[type="password"] {
			background-color: #fafafa;
			border-width: 1px;
			border-style: solid;
			border-color: #666 #999 #ccc #999;
			background-image: url({if 'RELATIVE_WCF_DIR'|defined}{@' '|str_replace:'%20':RELATIVE_WCF_DIR}acp/images/setupInputBackground.png{else}install.php?showImage=setupInputBackground.png&tmpFilePrefix={@$tmpFilePrefix}{/if});
			background-position: left top;
			background-repeat: repeat-x;
			min-height: 13px;
		}
		
		textarea, input[type="text"], input[type="password"] {
			width: 100%;
		}
	
		textarea:focus, select:focus, input[type="text"]:focus, input[type="password"]:focus {
			background-color: #fff9f4;
			border: 1px solid #fa2;
			background-image: url({if 'RELATIVE_WCF_DIR'|defined}{@' '|str_replace:'%20':RELATIVE_WCF_DIR}acp/images/setupInputBackground.png{else}install.php?showImage=setupInputBackground.png&tmpFilePrefix={@$tmpFilePrefix}{/if});
			background-repeat: repeat-x;
			outline: 0;
		}
		
		textarea, select, input[type="text"], input[type="password"] {
			padding: 2px;
			-webkit-border-radius: 3px;
			-moz-border-radius: 3px;
		}
					
		label {
			padding-bottom: 2px;
			display: block;
		}
		
		.setupIcon {
			margin-{if $__wcf->getLanguage()->getPageDirection() == 'ltr'}right{else}left{/if}: 30px;
			float: {if $__wcf->getLanguage()->getPageDirection() == 'ltr'}left{else}right{/if};
		}
		
		.nextButton {
			float: {if $__wcf->getLanguage()->getPageDirection() == 'ltr'}right{else}left{/if};
		}
		
		.copyright {
			font-size: .8em;
		}
		
		.left {
			float: {if $__wcf->getLanguage()->getPageDirection() == 'ltr'}left{else}right{/if};
		}
		
		.left, .right {
			font-weight: bold;
			display: block;
		}
		
		.right {
			margin-{if $__wcf->getLanguage()->getPageDirection() == 'ltr'}left{else}right{/if}: 48%;
			width: 48%;
		}
		
		.disabled {
			color: #b2b2b2;
		}
		
		.error {
			color: #c00;
			border: 1px solid #c00;
			background-color: #fee;
			padding: 4px 10px;
		}
		
		.errorField {
			color: #c00;
		}
		
		.errorField .inputText, .errorField select, .errorField textarea {
			border: 1px solid #c00;
			background-color: #fee;
		}
					
		/*]]>*/
	</style>
	<!-- /Stylesheets -->
</head>

<body>
	<a id="top"></a>
	<div class="page">
		<!-- ToDo: new images & icons 
		<img class="setupIcon" src="{if 'RELATIVE_WCF_DIR'|defined}{@RELATIVE_WCF_DIR}acp/images/setupIconXL.jpg{else}install.php?showImage=setupIconXL.jpg&amp;tmpFilePrefix={@$tmpFilePrefix}{/if}" alt="" />
		-->
		<h1>{lang}wcf.global.title{/lang}</h1>
		<div class="progress">
			<div class="progressBar" style="width: {@300*$progress/100|round:0}px"></div>
			<div class="progressText">{lang}wcf.global.progressBar{/lang}</div>
		</div>
			