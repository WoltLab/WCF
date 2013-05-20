<div class="tabMenuContainer" data-active="{$activeTabMenuItem}" data-store="activeTabMenuItem">
	<nav class="tabMenu jsOnly">
		<ul>
			{if MODULE_SMILEY && $smileyCategories|count}<li id="smiliesTab"><a href="{@$__wcf->getAnchor('smilies')}" title="{lang}wcf.message.smilies{/lang}">{lang}wcf.message.smilies{/lang}</a></li>{/if}
			{if MODULE_ATTACHMENT && $attachmentHandler !== null && $attachmentHandler->canUpload()}<li id="attachmentsTab"><a href="{@$__wcf->getAnchor('attachments')}" title="{lang}wcf.attachment.attachments{/lang}">{lang}wcf.attachment.attachments{/lang}</a></li>{/if}
			<li><a href="{@$__wcf->getAnchor('settings')}" title="{lang}wcf.message.settings{/lang}">{lang}wcf.message.settings{/lang}</a></li>
			{event name='tabMenuTabs'}
		</ul>
	</nav>
	
	{if MODULE_SMILEY && $smileyCategories|count}{include file='messageFormSmilies'}{/if}
	{if MODULE_ATTACHMENT && $attachmentHandler !== null && $attachmentHandler->canUpload()}{include file='messageFormAttachments'}{/if}
	
	{include file='messageFormSettings'}
	
	{event name='tabMenuContents'}
</div>

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		if (jQuery.browser.mobile) $('#smiliesTab, #smilies').remove();
		WCF.TabMenu.init();
	});
	//]]>
</script>