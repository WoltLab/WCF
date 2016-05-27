<textarea name="content[{@$languageID}]" id="content{@$languageID}">{if !$content[$languageID]|empty}{$content[$languageID]}{/if}</textarea>
{if $pageType == 'text'}
	{include file='wysiwyg' wysiwygSelector='content'|concat:$languageID}
{elseif $pageType == 'html'}
	{include file='codemirror' codemirrorMode='htmlmixed' codemirrorSelector='#content'|concat:$languageID}
{elseif $pageType == 'tpl'}
	{include file='codemirror' codemirrorMode='smartymixed' codemirrorSelector='#content'|concat:$languageID}
{/if}
