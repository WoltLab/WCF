{* the settings template does not generate direct ouput anymore, but captures it content *}
{include file='messageFormSettings'}

<div class="messageTabMenu" data-preselect="{if $preselectTabMenu|isset}{$preselectTabMenu}{else}true{/if}" data-wysiwyg-container-id="{if $wysiwygContainerID|isset}{$wysiwygContainerID}{else}text{/if}">
	<nav class="messageTabMenuNavigation jsOnly">
		<ul>
			{if MODULE_SMILEY && !$smileyCategories|empty}<li data-name="smilies"><a><span class="icon icon16 fa-smile-o"></span> <span>{lang}wcf.message.smilies{/lang}</span></a></li>{/if}
			{if MODULE_ATTACHMENT && !$attachmentHandler|empty && $attachmentHandler->canUpload()}<li data-name="attachments"><a><span class="icon icon16 fa-paperclip"></span> <span>{lang}wcf.attachment.attachments{/lang}</span></a></li>{/if}
			{if $__messageFormSettings}<li data-name="settings"><a><span class="icon icon16 fa-cog"></span> <span>{lang}wcf.message.settings{/lang}</span></a></li>{/if}
			{if $__showPoll|isset && $__showPoll}<li data-name="poll"><a><span class="icon icon16 fa-bar-chart"></span> <span>{lang}wcf.poll.management{/lang}</span></a></li>{/if}
			{event name='tabMenuTabs'}
		</ul>
	</nav>
	
	{if MODULE_SMILEY && !$smileyCategories|empty}{include file='messageFormSmilies'}{/if}
	{if MODULE_ATTACHMENT && !$attachmentHandler|empty && $attachmentHandler->canUpload()}{include file='messageFormAttachments'}{/if}
	
	{if $__messageFormSettings}{@$__messageFormSettings}{/if}
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
