{assign var=smileyCategories value=$__wcf->getSmileyCache()->getVisibleCategories()}
{if !$permissionCanUseSmilies|isset}{assign var=permissionCanUseSmilies value='user.message.canUseSmilies'}{/if}
{if !$wysiwygContainerID|isset}{assign var=wysiwygContainerID value='text'}{/if}
{if !$wysiwygSelector|isset}{assign var=wysiwygSelector value=$wysiwygContainerID}{/if}

{capture assign='__messageFormSettingsInlineContent'}{include file='messageFormSettingsInline'}{/capture}
{assign var='__messageFormSettingsInlineContent' value=$__messageFormSettingsInlineContent|trim}

<div class="messageTabMenu"{if $preselectTabMenu|isset} data-preselect="{$preselectTabMenu}"{/if} data-wysiwyg-container-id="{$wysiwygContainerID}">
	<nav class="messageTabMenuNavigation jsOnly">
		<ul>
			{if MODULE_SMILEY && $__wcf->getSession()->getPermission($permissionCanUseSmilies) && $smileyCategories|count}<li data-name="smilies"><a><span class="icon icon16 fa-smile-o"></span> <span>{lang}wcf.message.smilies{/lang}</span></a></li>{/if}
			{if MODULE_ATTACHMENT && !$attachmentHandler|empty && $attachmentHandler->canUpload()}<li data-name="attachments"><a><span class="icon icon16 fa-paperclip"></span> <span>{lang}wcf.attachment.attachments{/lang}</span></a></li>{/if}
			{if $__messageFormSettingsInlineContent}<li data-name="settings"><a><span class="icon icon16 fa-cog"></span> <span>{lang}wcf.message.settings{/lang}</span></a></li>{/if}
			{if $__showPoll|isset && $__showPoll}<li data-name="poll"><a><span class="icon icon16 fa-bar-chart"></span> <span>{lang}wcf.poll.management{/lang}</span></a></li>{/if}
			{event name='tabMenuTabs'}
		</ul>
	</nav>
	
	{if MODULE_SMILEY && $__wcf->getSession()->getPermission($permissionCanUseSmilies) && $smileyCategories|count}{include file='messageFormSmilies'}{/if}
	{if MODULE_ATTACHMENT && !$attachmentHandler|empty && $attachmentHandler->canUpload()}{include file='messageFormAttachments'}{/if}
	
	{if $__messageFormSettingsInlineContent}{@$__messageFormSettingsInlineContent}{/if}
	
	{include file='__messageFormPollInline'}
	
	{event name='tabMenuContents'}
</div>

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		$('.messageTabMenu').messageTabMenu();
	});
	//]]>
</script>
