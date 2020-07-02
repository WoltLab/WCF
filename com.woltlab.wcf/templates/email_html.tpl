<html>
	<head>
		<style type="text/css">
		*:not(html):not(head):not(meta):not(br) {
			font-family: {@$style->getVariable('wcfFontFamilyFallback', true)};
			font-size: {$style->getVariable('wcfFontSizeDefault')};
		}
		
		html, body, h1, h2, h3 {
			padding: 0;
			margin: 0;
		}
		
		body {
			background-color: {$style->getVariable('wcfContentBackground', true)};
			color: {$style->getVariable('wcfContentText', true)};
		}
		
		a {
			color: {$style->getVariable('wcfContentLink', true)};
			text-decoration: none;
		}
		
		p, .paragraphMargin {
			margin-top: 1em;
			margin-bottom: 1em;
		}
		
		td {
			vertical-align: top;
		}
		
		{* see email_paddingHelper.tpl *}
		table.paddingHelper.block {
			width: 100%;
		}
		
		.content {
			padding: 40px 40px 60px;
		}
		.header, .footer {
			padding: 20px 40px;
		}
		.header {
			background-color: {$style->getVariable('wcfHeaderBackground', true)};
			color: {$style->getVariable('wcfHeaderText', true)};
		}
		.footer {
			background-color: {$style->getVariable('wcfFooterBackground', true)};
			color: {$style->getVariable('wcfFooterText', true)};
		}
		.footer a {
			color: {$style->getVariable('wcfFooterLink', true)};
		}
		h1 {
			font-weight: 300;
			line-height: 1.05;
			font-size: {$style->getVariable('wcfFontSizeTitle')};
		}
		h2 {
			font-weight: 400;
			line-height: 1.28;
			color: {$style->getVariable('wcfContentHeadlineText')};
			font-size: {$style->getVariable('wcfFontSizeSection')};
		}
		small {
			font-size: {$style->getVariable('wcfFontSizeSmall')};
			font-weight: 300;
		}
		
		.largeMarginTop {
			margin-top: 40px;
		}
		
		{* Buttons *}
		td.button {
			background-color: {$style->getVariable('wcfButtonPrimaryBackground', true)};
			border-radius: 2px;
			padding: 6px 12px;
		}
		td.button a {
			color: {$style->getVariable('wcfButtonPrimaryText', true)};
		}
		
		.box128 {
			border-top: 1px solid {$style->getVariable('wcfContentBorder', true)};
			border-bottom: 1px solid {$style->getVariable('wcfContentBorder', true)};
			padding: 20px;
		}
		
		.box128 td.boxContent {
			padding-left: 20px;
		}
		
		.box64 {
			border-top: 1px solid {$style->getVariable('wcfContentBorder', true)};
			border-bottom: 1px solid {$style->getVariable('wcfContentBorder', true)};
			padding: 15px;
		}
		
		.box64 td.boxContent {
			padding-left: 15px;
		}
		
		.box48 {
			border-top: 1px solid {$style->getVariable('wcfContentBorder', true)};
			border-bottom: 1px solid {$style->getVariable('wcfContentBorder', true)};
			padding: 12px;
		}
		
		.box48 td.boxContent {
			padding-left: 12px;
		}
		
		.box32 {
			border-top: 1px solid {$style->getVariable('wcfContentBorder', true)};
			border-bottom: 1px solid {$style->getVariable('wcfContentBorder', true)};
			padding: 10px;
		}
		
		.box32 td.boxContent {
			padding-left: 10px;
		}
		
		.containerHeadline h3 {
			margin: 0;
			padding: 0;
			font-weight: 400;
			line-height: 1.28;
			font-size: {$style->getVariable('wcfFontSizeHeadline')};
		}

		.containerHeadline h3 a {
			font-size: {$style->getVariable('wcfFontSizeHeadline')};
		}
		
		.userAvatarImage {
			background-color: #fff;
			border-radius: 50%;
		}
		</style>
	</head>
	<body>
	{capture assign='header'}
	<h1>{@PAGE_TITLE|language}</h1>
	{/capture}
	{include file='email_paddingHelper' block=true class='header' content=$header sandbox=true}
	
	{if $beforeContent|isset}{@$beforeContent}{/if}
	
	{include file='email_paddingHelper' block=true class='content' content=$content sandbox=true}
	
	{if $afterContent|isset}{@$afterContent}{/if}
	
	{capture assign='footer'}
	{hascontent}
	<span style="font-size: 0;">-- <br></span>
	{content}
	{if MAIL_SIGNATURE_HTML|language}
	{@MAIL_SIGNATURE_HTML|language}
	{else}
	{@MAIL_SIGNATURE|language|newlineToBreak}
	{/if}
	{/content}{/hascontent}{/capture}
	{include file='email_paddingHelper' block=true class='footer' content=$footer sandbox=true}
	
	<table>{* Do not remove: This table is needed by certain less able email clients to properly support background colors. Don't ask. *}</table>
	</body>
</html>
