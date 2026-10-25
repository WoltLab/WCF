{hascontent}
	<div id="settings_{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}" class="messageTabMenuContent">
		{content}
			{event name='beforeSettings'}
			
			{capture assign='__messageFormSettingsInlineSettings'}{event name='settings'}{/capture}
			{assign var='__messageFormSettingsInlineSettings' value=$__messageFormSettingsInlineSettings|trim}
			
			{if $__messageFormSettingsInlineSettings}
				<dl>
					{unsafe:$__messageFormSettingsInlineSettings}
				</dl>
			{/if}
			
			{event name='afterSettings'}
		{/content}
	</div>
{/hascontent}
