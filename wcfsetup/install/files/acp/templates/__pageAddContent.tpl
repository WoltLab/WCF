<textarea name="content[{@$languageID}]" id="content{@$languageID}">{if !$content[$languageID]|empty}{$content[$languageID]}{/if}</textarea>
{if $pageType == 'text'}
	{capture append='__redactorJavaScript'}, '{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabPage.js?v={@LAST_UPDATE_TIME}'{/capture}
	{capture append='__redactorConfig'}
		Language.addObject({
			'wcf.page.search': '{lang}wcf.page.search{/lang}',
			'wcf.page.search.error.tooShort': '{lang}wcf.page.search.error.tooShort{/lang}',
			'wcf.page.search.error.noResults': '{lang}wcf.page.search.error.noResults{/lang}',
			'wcf.page.search.name': '{lang}wcf.page.search.name{/lang}',
			'wcf.page.search.results': '{lang}wcf.page.search.results{/lang}',
			'wcf.page.search.results.description': '{lang}wcf.page.search.results.description{/lang}'
		});
		
		buttonOptions.woltlabPage = { icon: 'fa-file-text-o', title: '{lang}wcf.editor.button.page{/lang}' };
		
		buttons.push('woltlabPage');
		
		config.plugins.push('WoltLabPage');
	{/capture}
	
	{include file='wysiwyg' wysiwygSelector='content'|concat:$languageID}
{elseif $pageType == 'html'}
	{include file='codemirror' codemirrorMode='htmlmixed' codemirrorSelector='#content'|concat:$languageID}
{elseif $pageType == 'tpl'}
	{include file='codemirror' codemirrorMode='smartymixed' codemirrorSelector='#content'|concat:$languageID}
{/if}
