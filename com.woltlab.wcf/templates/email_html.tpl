<html>
	<head>
		<style type="text/css">
		* {
			font-family: {@$style->getVariable('wcfFontFamilyFallback', true)};
		}
		html {
			padding: 0;
			margin: 0;
		}
		body {
			padding: 0;
			margin: 0;
			background-color: {$style->getVariable('wcfContentBackground', true)};
		}
		
		a {
			color: {$style->getVariable('wcfContentLink', true)};
			text-decoration: none;
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
		.header {
			background-color: {$style->getVariable('wcfHeaderBackground', true)};
			color: {$style->getVariable('wcfHeaderText', true)};
			padding: 20px 10px;
		}
		.footer {
			background-color: {$style->getVariable('wcfFooterBackground', true)};
			color: {$style->getVariable('wcfFooterText', true)};
			padding: 20px 10px;
		}
		h1 {
			font-weight: 300;
			line-height: 1.05;
			font-size: {$style->getVariable('wcfFontSizeTitle')};
			color: {$style->getVariable('wcfContentHeadlineText')};
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
		
		.containerHeadline h3 {
			margin: 0;
			padding: 0;
			font-weight: 400;
		}
		</style>
	</head>
	<body>
	{capture assign='header'}
	{/capture}
	{include file='email_paddingHelper' block=true class='header' content=$header sandbox=true}
	
	{if $beforeContent|isset}{@$beforeContent}{/if}
	
	{include file='email_paddingHelper' block=true class='content' content=$content sandbox=true}
	
	{if $afterContent|isset}{@$afterContent}{/if}
	
	{capture assign='footer'}
	{hascontent}
	<span style="font-size: 0;">-- <br></span>
	{content}
	{@MAIL_SIGNATURE|language|nl2br}
	{if $mailbox|is_a:'wcf\system\email\UserMailbox'}
	{if MAIL_SIGNATURE|language}<br><br>{/if}
	This email was sent to you, because you registered on the {$mailbox->getUser()->registrationDate|plainTime} at {@PAGE_TITLE|language}.{/if} {* TODO: language item *}
	{/content}
	{/hascontent}
	{/capture}
	{include file='email_paddingHelper' block=true class='footer' content=$footer sandbox=true}
	
	<table>{* Do not remove: This table is needed by certain less able email clients to properly support background colors. Don't ask. *}</table>
	</body>
</html>
