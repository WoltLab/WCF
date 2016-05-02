<textarea name="content[{@$languageID}]" id="content{@$languageID}">{if !$content[$languageID]|empty}{$content[$languageID]}{/if}</textarea>
{if $pageType == 'text'}
	{capture assign='wysiwygSelector'}content{@$languageID}{/capture}
	
	{include file='wysiwyg' wysiwygSelector=$wysiwygSelector}
{elseif $pageType == 'html'}
	{capture assign='codemirrorSelector'}#content{@$languageID}{/capture}
	
	{include file='codemirror' codemirrorMode='htmlmixed' codemirrorSelector=$codemirrorSelector}
{elseif $pageType == 'tpl'}
	{capture assign='codemirrorSelector'}#content{@$languageID}{/capture}
	
	{include file='codemirror' codemirrorMode='smartymixed' codemirrorSelector=$codemirrorSelector}
{/if}