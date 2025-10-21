{assign var=smileyCategories value=$__wcf->getSmileyCache()->getVisibleCategories()}
{if !$wysiwygContainerID|isset}{assign var=wysiwygContainerID value='text'}{/if}
{if !$wysiwygSelector|isset}{assign var=wysiwygSelector value=$wysiwygContainerID}{/if}

{capture assign='__messageFormSettingsInlineContent'}{include file='messageFormSettingsInline'}{/capture}
{assign var='__messageFormSettingsInlineContent' value=$__messageFormSettingsInlineContent|trim}

<div class="messageTabMenu" {if $preselectTabMenu|isset} data-preselect="{$preselectTabMenu}" {/if}
	data-wysiwyg-container-id="{$wysiwygSelector}">
	<nav class="messageTabMenuNavigation jsOnly">
		<ul>
			{if MODULE_SMILEY && !$smileyCategories|empty}
				<li data-name="smilies">
					<button type="button">
						{icon name='face-smile'}
						<span>{lang}wcf.message.smilies{/lang}</span>
					</button>
				</li>
			{/if}
			{if !$attachmentHandler|empty && $attachmentHandler->canUpload()}
				<li data-name="attachments">
					<button type="button">
						{icon name='paperclip'}
						<span>{lang}wcf.attachment.attachments{/lang}</span>
						{if $attachmentHandler->count() > 0}
							<span class="badge badgeUpdate">{#$attachmentHandler->count()}</span>
						{/if}
					</button>
				</li>
			{/if}
			{if $__messageFormSettingsInlineContent}
				<li data-name="settings"><button type="button">
						{icon name='gear'}
						<span>{lang}wcf.message.settings{/lang}</span>
					</button>
				</li>
			{/if}
			{if $__showPoll|isset && $__showPoll}
				<li data-name="poll">
					<button type="button">
						{icon name='chart-bar'}
						<span>{lang}wcf.poll.management{/lang}</span>
					</button>
				</li>
			{/if}
			{event name='tabMenuTabs'}

			<li data-name="quotes" hidden>
				<button type="button">
					{icon name='quote-left'}
					<span>{lang}wcf.bbcode.quote.messageTab{/lang}</span>
				</button>
			</li>
		</ul>
	</nav>

	{if MODULE_SMILEY && !$smileyCategories|empty}{include file='shared_messageFormSmileyTab'}{/if}
	{if !$attachmentHandler|empty && $attachmentHandler->canUpload()}
		{include file='shared_messageFormAttachments'}
	{/if}

	{if $__messageFormSettingsInlineContent}{unsafe:$__messageFormSettingsInlineContent}{/if}

	{include file='__messageFormPollInline'}

	{event name='tabMenuContents'}

	{include file='__messageFormQuote'}
</div>
