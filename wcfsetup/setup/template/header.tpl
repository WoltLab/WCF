<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="{lang}wcf.global.pageDirection{/lang}" xml:lang="{@LANGUAGE_CODE}">
	<head>
		<title>{lang}wcf.global.progressBar{/lang} - {lang}wcf.global.pageTitle{/lang}</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<style type="text/css">
			/*<![CDATA[*/
			body {
				font-family: "Trebuchet MS", Tahoma, Verdana, Arial, Helvetica, sans-serif; 
				color: #333;
				font-size: .82em;
				margin: 0;
				padding: 0;
				background-color: #eee;
				background-image: url({if 'RELATIVE_WCF_DIR'|defined}{@' '|str_replace:'%20':RELATIVE_WCF_DIR}acp/images/setupBackground-{@PAGE_DIRECTION}.png{else}install.php?showImage=setupBackground-{@PAGE_DIRECTION}.png&tmpFilePrefix={@$tmpFilePrefix}{/if});
				background-repeat: repeat-y;
				background-position: {if PAGE_DIRECTION == 'ltr'}left{else}right{/if};
			}
			
			.page {
				background-image: url({if 'RELATIVE_WCF_DIR'|defined}{@' '|str_replace:'%20':RELATIVE_WCF_DIR}acp/images/setupHeader-{@PAGE_DIRECTION}.jpg{else}install.php?showImage=setupHeader-{@PAGE_DIRECTION}.jpg&tmpFilePrefix={@$tmpFilePrefix}{/if});
				background-repeat: no-repeat;
				background-color: #fff;
				padding: 153px 40px 20px 40px;
				width: 720px;
			}
			
			.inner {
				padding: 10px 20px;
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
			
			h3 {
				font-size: 1.3em;
				font-weight: bold;
				color: #666;
				margin: 0;
				padding: 2px 0 10px 0;
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
				padding-{if PAGE_DIRECTION == 'ltr'}right{else}left{/if}: 3%;
				float: {if PAGE_DIRECTION == 'ltr'}left{else}right{/if};
				width: 30%;
			}
			
			hr {
				color: #8da4b7;
				background-color: #8da4b7;
				border: 0;
				margin: 10px 0;
				padding: 0;
				height: 1px;
				width: 100%;
				position: relative;
				clear: {if PAGE_DIRECTION == 'ltr'}left{else}right{/if};
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
				margin-{if PAGE_DIRECTION == 'ltr'}right{else}left{/if}: 30px;
				float: {if PAGE_DIRECTION == 'ltr'}left{else}right{/if};
			}
			
			.nextButton {
				float: {if PAGE_DIRECTION == 'ltr'}right{else}left{/if};
			}
			
			.copyright {
				font-size: .8em;
			}
			
			.left {
				float: {if PAGE_DIRECTION == 'ltr'}left{else}right{/if};
			}
			
			.left, .right {
				font-weight: bold;
				display: block;
			}
			
			.right {
				margin-{if PAGE_DIRECTION == 'ltr'}left{else}right{/if}: 48%;
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
			
			.progress {
				border: 1px solid #b2b2b2;
				margin: {if PAGE_DIRECTION == 'ltr'}0 0 25px 128px{else}0 128px 25px 0{/if};
				padding: 1px;
				width: 300px;
				height: 16px;
				-webkit-border-radius: 3px;
				-moz-border-radius: 3px;
			}
			
			.progressBar {
				background-color: #f1f1f1;
				border-bottom: 8px solid #ddd;
				height: 8px;
				font-size: 7px;
				-webkit-border-radius: 1px;
				-moz-border-radius: 1px;
			}
			
			.progressText {
				color: #333;
				text-shadow: 0 1px 1px #fff;
				font-size: .75em;
				text-align: center;
				margin-top: -15px;
			}
			
			#wcfUrl {
				text-decoration: underline;
				background: transparent;
				border: none;
				padding: 0;
			}
						
			/*]]>*/
		</style>
		<!--[if lt IE 7]>
			<style type="text/css">
				.page {
					width: 800px;
				}
				.progressText {
					margin-top: -16px;
				}
			</style>
		<![endif]-->
		<!--[if IE]>
			<style type="text/css">
				hr {
					margin: 0;
				}
				fieldset p, fieldset div {
					min-height: 0;
				}
			</style>
		<![endif]-->
	</head>
<body>
	<div class="page">
		<img class="setupIcon" src="{if 'RELATIVE_WCF_DIR'|defined}{@RELATIVE_WCF_DIR}acp/images/setupIconXL.jpg{else}install.php?showImage=setupIconXL.jpg&amp;tmpFilePrefix={@$tmpFilePrefix}{/if}" alt="" />
		<h1>{lang}wcf.global.title{/lang}</h1>
		<div class="progress">
			<div class="progressBar" style="width: {@300*$progress/100|round:0}px"></div>
			<div class="progressText">{lang}wcf.global.progressBar{/lang}</div>
		</div>
		<hr />
	
	