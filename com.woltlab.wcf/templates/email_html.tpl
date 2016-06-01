<html>
	<body>
	<div style="text-align: center;">
		<a href="{link}{/link}">
			{if $__wcf->getStyleHandler()->getStyle()->getPageLogo()}
				<img src="{$__wcf->getStyleHandler()->getStyle()->getPageLogo()}" alt="">
			{/if}
			{event name='headerLogo'}
		</a>
	</div>
	<div class="content">{@$content}</div>
	{hascontent}
	<div class="signature" style="color: grey; font-size: 10px;">
	-- <br>
	{content}
	{@MAIL_SIGNATURE|language}
	{if $mailbox|is_a:'wcf\system\email\UserMailbox'}
	{if MAIL_SIGNATURE|language}<br><br>{/if}
	This email was sent to you, because you registered on the {$mailbox->getUser()->registrationDate|plainTime} at {@PAGE_TITLE|language}.{/if} {* TODO: language item *}
	{/content}
	</div>
	{/hascontent}
	
	<table>{* Do not remove: This table is needed by certain less able email clients to properly support background colors. Don't ask. *}</table>
	</body>
</html>
