{hascontent}
	<div class="messageTabMenu" data-preselect="{if $preselectTabMenu|isset}{$preselectTabMenu}{else}false{/if}" data-wysiwyg-container-id="{if $wysiwygContainerID|isset}{$wysiwygContainerID}{else}text{/if}">
		<nav class="messageTabMenuNavigation jsOnly">
			<ul>
				{content}
					{if MODULE_SMILEY && !$smileyCategories|empty}<li data-name="smilies"><a>{icon name='face-smile'} <span>{lang}wcf.message.smilies{/lang}</span></a></li>{/if}
					{if !$attachmentHandler|empty && $attachmentHandler->canUpload()}
						<li data-name="attachments"><a>{icon name='paperclip'} <span>{lang}wcf.attachment.attachments{/lang}</span></a></li>
					{/if}
					{event name='tabMenuTabs'}
				{/content}
			</ul>
		</nav>
		
		{if MODULE_SMILEY && !$smileyCategories|empty}{include file='messageFormSmilies'}{/if}
		{if !$attachmentHandler|empty && $attachmentHandler->canUpload()}
			{include file='shared_messageFormAttachments'}
		{/if}
		
		{event name='tabMenuContents'}
	</div>
	
	<script data-relocate="true">
		$(function() {
			$('.messageTabMenu').messageTabMenu();
		});
	</script>
{/hascontent}
