{assign var='__pageContentID' value='content'|concat:$languageID}

{if $pageType == 'html' || $pageType == 'tpl'}
	<ul class="codemirrorToolbar">
		<li><button type="button" id="codemirror-{@$__pageContentID}-media" class="jsTooltip" title="{lang}wcf.editor.button.media{/lang}">{icon name='file'}</button></li>
		<li><button type="button" id="codemirror-{@$__pageContentID}-page" class="jsTooltip" title="{lang}wcf.editor.button.page{/lang}">{icon name='file-lines'}</button></li>
	</ul>
	<script data-relocate="true">
		{include file='mediaJavaScript'}
		
		require([
			'Language',
			'WoltLabSuite/Core/Acp/Ui/CodeMirror/Media',
			'WoltLabSuite/Core/Acp/Ui/CodeMirror/Page'
		], function(
			Language,
			AcpUiCodeMirrorMedia,
			AcpUiCodeMirrorPage
		) {
			Language.addObject({
				'wcf.page.search': '{jslang}wcf.page.search{/jslang}',
				'wcf.page.search.error.tooShort': '{jslang}wcf.page.search.error.tooShort{/jslang}',
				'wcf.page.search.error.noResults': '{jslang}wcf.page.search.error.noResults{/jslang}',
				'wcf.page.search.name': '{jslang}wcf.page.search.name{/jslang}',
				'wcf.page.search.results': '{jslang}wcf.page.search.results{/jslang}',
			});
			
			new AcpUiCodeMirrorMedia('{@$__pageContentID}');
			new AcpUiCodeMirrorPage('{@$__pageContentID}');
		});
	</script>
{/if}

{if $pageType == 'text'}
	<textarea name="content[{@$languageID}]" id="{@$__pageContentID}"
		{if $pageType == 'text'}
			class="wysiwygTextarea" data-disable-attachments="true" data-autosave="com.woltlab.wcf.page{$action|ucfirst}-{if $action == 'edit'}{@$pageID}{else}0{/if}-{@$languageID}"
			{if $action === 'edit'}data-autosave-last-edit-time="{@$page->lastUpdateTime}"{/if}
		{/if}
	>{if !$content[$languageID]|empty}{$content[$languageID]}{/if}</textarea>
	{include file='shared_wysiwygCmsToolbar' wysiwygSelector=$__pageContentID}
	{include file='shared_wysiwyg' wysiwygSelector=$__pageContentID}
{else}
	<div dir="ltr">
		<textarea name="content[{@$languageID}]" id="{@$__pageContentID}"
		        {if $pageType == 'text'}
				class="wysiwygTextarea" data-disable-attachments="true" data-autosave="com.woltlab.wcf.page{$action|ucfirst}-{if $action == 'edit'}{@$pageID}{else}0{/if}-{@$languageID}"
			        {if $action === 'edit'}data-autosave-last-edit-time="{@$page->lastUpdateTime}"{/if}
			{/if}
		>{if !$content[$languageID]|empty}{$content[$languageID]}{/if}</textarea>
	</div>
	{if $pageType == 'html'}
		{include file='shared_codemirror' codemirrorMode='htmlmixed' codemirrorSelector='#content'|concat:$languageID}
	{elseif $pageType == 'tpl'}
		{include file='shared_codemirror' codemirrorMode='smartymixed' codemirrorSelector='#content'|concat:$languageID}
	{/if}
{/if}
