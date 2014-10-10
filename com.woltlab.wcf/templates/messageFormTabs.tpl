<div class="messageTabMenu" data-preselect="{if $preselectTabMenu|isset}{$preselectTabMenu}{else}true{/if}" data-wysiwyg-container-id="{if $wysiwygContainerID|isset}{$wysiwygContainerID}{else}text{/if}">
	<nav class="messageTabMenuNavigation jsOnly">
		<ul>
			{if MODULE_SMILEY && $__wcf->getSession()->getPermission($permissionCanUseSmilies) && $smileyCategories|count}<li data-name="smilies"><a>{lang}wcf.message.smilies{/lang}</a></li>{/if}
			{if MODULE_ATTACHMENT && !$attachmentHandler|empty && $attachmentHandler->canUpload()}<li data-name="attachments"><a>{lang}wcf.attachment.attachments{/lang}</a></li>{/if}
			<li data-name="settings"><a>{lang}wcf.message.settings{/lang}</a></li>
			{if $__showPoll|isset && $__showPoll}<li data-name="poll"><a>{lang}wcf.poll.management{/lang}</a></li>{/if}
			{event name='tabMenuTabs'}
		</ul>
	</nav>
	
	{if MODULE_SMILEY && $__wcf->getSession()->getPermission($permissionCanUseSmilies) && $smileyCategories|count}{include file='messageFormSmilies'}{/if}
	{if MODULE_ATTACHMENT && !$attachmentHandler|empty && $attachmentHandler->canUpload()}{include file='messageFormAttachments'}{/if}
	
	{include file='messageFormSettings'}
	{include file='__messageFormPoll'}
	
	{event name='tabMenuContents'}
</div>

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		$('.messageTabMenu').messageTabMenu();
	});
	//]]>
</script>
