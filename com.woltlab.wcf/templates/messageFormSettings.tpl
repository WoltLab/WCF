{capture assign='__messageFormSettings'}
	{hascontent}
		<div id="settings_{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}" class="settingsContent messageTabMenuContent">
			<dl class="wide">
				{content}{event name='settings'}{/content}
			</dl>
		</div>
	{/hascontent}
{/capture}
{assign var='__messageFormSettings' value=$__messageFormSettings|trim}
