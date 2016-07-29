{assign var='__pageContentID' value='content'|concat:$languageID}

<script data-relocate="true">
	require(['Language'], function (Language) {
		Language.addObject({
			'wcf.page.search': '{lang}wcf.page.search{/lang}',
			'wcf.page.search.error.tooShort': '{lang}wcf.page.search.error.tooShort{/lang}',
			'wcf.page.search.error.noResults': '{lang}wcf.page.search.error.noResults{/lang}',
			'wcf.page.search.name': '{lang}wcf.page.search.name{/lang}',
			'wcf.page.search.results': '{lang}wcf.page.search.results{/lang}',
			'wcf.page.search.results.description': '{lang}wcf.page.search.results.description{/lang}'
		});
	})
</script>

{if $pageType == 'html' || $pageType == 'tpl'}
	<ul class="codemirrorToolbar">
		<li><a href="#" id="codemirror-{@$__pageContentID}-media" class="jsTooltip" title="{lang}wcf.editor.button.media{/lang}"><span class="icon icon16 fa-file-o"></span></a></li>
		<li><a href="#" id="codemirror-{@$__pageContentID}-page" class="jsTooltip" title="{lang}wcf.editor.button.page{/lang}"><span class="icon icon16 fa-file-text-o"></span></a></li>
	</ul>
	<script data-relocate="true">
		require(['WoltLab/WCF/Acp/Ui/CodeMirror/Media', 'WoltLab/WCF/Acp/Ui/CodeMirror/Page'], function(AcpUiCodeMirrorMedia, AcpUiCodeMirrorPage) {
			new AcpUiCodeMirrorMedia('{@$__pageContentID}');
			new AcpUiCodeMirrorPage('{@$__pageContentID}');
		});
	</script>
{/if}

<textarea name="content[{@$languageID}]" id="{@$__pageContentID}"
          {if $pageType == 'text'}
	          class="wysiwygTextarea" data-autosave="com.woltlab.wcf.page{$action|ucfirst}-{if $action == 'edit'}{@$pageID}{else}0{/if}-{@$languageID}"
          {/if}
>{if !$content[$languageID]|empty}{$content[$languageID]}{/if}</textarea>
{if $pageType == 'text'}
	{capture append='__redactorJavaScript'}, '{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabPage.js?v={@LAST_UPDATE_TIME}'{/capture}
	{capture append='__redactorConfig'}
		buttonOptions.woltlabPage = { icon: 'fa-file-text-o', title: '{lang}wcf.editor.button.page{/lang}' };
		
		buttons.push('woltlabPage');
		
		config.plugins.push('WoltLabPage');
	{/capture}
	
	{include file='wysiwyg' wysiwygSelector=$__pageContentID}
{elseif $pageType == 'html'}
	{include file='codemirror' codemirrorMode='htmlmixed' codemirrorSelector='#content'|concat:$languageID}
{elseif $pageType == 'tpl'}
	{include file='codemirror' codemirrorMode='smartymixed' codemirrorSelector='#content'|concat:$languageID}
{/if}
