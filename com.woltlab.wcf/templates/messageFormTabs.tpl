<div class="tabMenuContainer" data-active="{$activeTabMenuItem}" data-store="activeTabMenuItem">
	<nav class="tabMenu jsOnly">
		<ul>
			{if MODULE_SMILEY && $__wcf->getSession()->getPermission($permissionCanUseSmilies) && $smileyCategories|count}<li id="smiliesTab"><a href="{@$__wcf->getAnchor('smilies')}" title="{lang}wcf.message.smilies{/lang}">{lang}wcf.message.smilies{/lang}</a></li>{/if}
			{if MODULE_ATTACHMENT && $attachmentHandler !== null && $attachmentHandler->canUpload()}<li id="attachmentsTab"><a href="{@$__wcf->getAnchor('attachments')}" title="{lang}wcf.attachment.attachments{/lang}">{lang}wcf.attachment.attachments{/lang}</a></li>{/if}
			<li><a href="{@$__wcf->getAnchor('settings')}" title="{lang}wcf.message.settings{/lang}">{lang}wcf.message.settings{/lang}</a></li>
			{if $__showPoll|isset && $__showPoll}<li><a href="{@$__wcf->getAnchor('poll')}" title="{lang}wcf.poll.management{/lang}">{lang}wcf.poll.management{/lang}</a></li>{/if}
			{event name='tabMenuTabs'}
		</ul>
	</nav>
	
	{if MODULE_SMILEY && $__wcf->getSession()->getPermission($permissionCanUseSmilies) && $smileyCategories|count}{include file='messageFormSmilies'}{/if}
	{if MODULE_ATTACHMENT && $attachmentHandler !== null && $attachmentHandler->canUpload()}{include file='messageFormAttachments'}{/if}
	
	{include file='messageFormSettings'}
	{include file='__messageFormPoll'}
	
	{event name='tabMenuContents'}
</div>

<script>
	//<![CDATA[
	$(function() {
		if (jQuery.browser.mobile) $('#smiliesTab, #smilies').remove();
		WCF.TabMenu.init();
	});
	//]]>
</script>