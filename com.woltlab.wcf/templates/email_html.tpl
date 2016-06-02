<html>
	<head>
		<style type="text/css">
		html {
			padding: 0;
			margin: 0;
			font-family: {@$style->getVariable('wcfFontFamilyFallback', true)};
		}
		body {
			padding: 0;
			margin: 0;
			background-color: {$style->getVariable('wcfContentBackground', true)};
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
		a {
			color: {$style->getVariable('wcfContentLink', true)};
			text-decoration: none;
		}
		a.button {
			background-color: {$style->getVariable('wcfButtonPrimaryBackground', true)};
			color: {$style->getVariable('wcfButtonPrimaryText', true)};
			border-radius: 2px;
			padding: 6px 12px;
		}
		</style>
	</head>
	<body>
	<div class="content">
		{@$content}
	</div>
	{hascontent}
	<div class="footer">
	-- <br>
	{content}
	{@MAIL_SIGNATURE|language|nl2br}
	{if $mailbox|is_a:'wcf\system\email\UserMailbox'}
	{if MAIL_SIGNATURE|language}<br><br>{/if}
	This email was sent to you, because you registered on the {$mailbox->getUser()->registrationDate|plainTime} at {@PAGE_TITLE|language}.{/if} {* TODO: language item *}
	{/content}
	</div>
	{/hascontent}
	
	<table>{* Do not remove: This table is needed by certain less able email clients to properly support background colors. Don't ask. *}</table>
	</body>
</html>
