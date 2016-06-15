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
		
		{* see email_paddingHelper.tpl *}
		table.paddingHelper.block {
			width: 100%;
		}
		
		.content {
			padding: 0 20px;
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
		
		{* Buttons *}
		td.button {
			background-color: {$style->getVariable('wcfButtonPrimaryBackground', true)};
			border-radius: 2px;
			padding: 6px 12px;
		}
		td.button a {
			color: {$style->getVariable('wcfButtonPrimaryText', true)};
		}
		</style>
	</head>
	<body>
	{if $beforeContent|isset}{@$beforeContent}{/if}
	<div class="content">
		{@$content}
	</div>
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
