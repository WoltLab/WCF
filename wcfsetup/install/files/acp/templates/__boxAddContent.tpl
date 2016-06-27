<textarea name="content[{@$languageID}]" id="content{@$languageID}">{if !$content[$languageID]|empty}{$content[$languageID]}{/if}</textarea>
{if $boxType == 'text'}
	{include file='wysiwyg' wysiwygSelector='content'|concat:$languageID}
{elseif $boxType == 'html'}
	{include file='codemirror' codemirrorMode='htmlmixed' codemirrorSelector='#content'|concat:$languageID}
{elseif $boxType == 'tpl'}
	{include file='codemirror' codemirrorMode='smartymixed' codemirrorSelector='#content'|concat:$languageID}
{/if}
