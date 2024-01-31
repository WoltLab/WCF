{assign var=smileyCategories value=$__wcf->getSmileyCache()->getVisibleCategories()}
{if !$wysiwygContainerID|isset}{assign var=wysiwygContainerID value='text'}{/if}
{if !$wysiwygSelector|isset}{assign var=wysiwygSelector value=$wysiwygContainerID}{/if}

{capture assign='__messageFormSettingsInlineContent'}{include file='messageFormSettingsInline'}{/capture}
{assign var='__messageFormSettingsInlineContent' value=$__messageFormSettingsInlineContent|trim}

<div class="messageTabMenu"{if $preselectTabMenu|isset} data-preselect="{$preselectTabMenu}"{/if} data-wysiwyg-container-id="{$wysiwygSelector}">
	<nav class="messageTabMenuNavigation jsOnly">
		<ul>
			{if MODULE_SMILEY && !$smileyCategories|empty}<li data-name="smilies"><a>{icon name='face-smile'} <span>{lang}wcf.message.smilies{/lang}</span></a></li>{/if}
			{if !$attachmentHandler|empty && $attachmentHandler->canUpload()}
				<li data-name="attachments"><a>{icon name='paperclip'} <span>{lang}wcf.attachment.attachments{/lang}</span></a></li>
			{/if}
			{if $__messageFormSettingsInlineContent}<li data-name="settings"><a>{icon name='gear'} <span>{lang}wcf.message.settings{/lang}</span></a></li>{/if}
			{if $__showPoll|isset && $__showPoll}<li data-name="poll"><a>{icon name='chart-bar'} <span>{lang}wcf.poll.management{/lang}</span></a></li>{/if}
			{event name='tabMenuTabs'}
		</ul>
	</nav>
	
	{if MODULE_SMILEY && !$smileyCategories|empty}{include file='messageFormSmilies'}{/if}
	{if !$attachmentHandler|empty && $attachmentHandler->canUpload()}
		{include file='shared_messageFormAttachments'}
	{/if}
	
	{if $__messageFormSettingsInlineContent}{@$__messageFormSettingsInlineContent}{/if}
	
	{include file='__messageFormPollInline'}
	
	{event name='tabMenuContents'}
</div>

<script data-relocate="true">
	$(function() {
		$('.messageTabMenu').messageTabMenu();
	});
</script>
