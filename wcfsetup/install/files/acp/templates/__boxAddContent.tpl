<script data-relocate="true">
	require(['Language'], function (Language) {
		Language.addObject({
			'wcf.page.search': '{lang}wcf.page.search{/lang}',
			'wcf.page.search.error.tooShort': '{lang}wcf.page.search.error.tooShort{/lang}',
			'wcf.page.search.error.noResults': '{lang}wcf.page.search.error.noResults{/lang}',
			'wcf.page.search.name': '{lang}wcf.page.search.name{/lang}',
			'wcf.page.search.results': '{lang}wcf.page.search.results{/lang}'
		});
	})
</script>

{if $boxType == 'html' || $boxType == 'tpl'}
	<ul class="codemirrorToolbar">
		<li><a href="#" id="codemirror-content{@$languageID}-media" class="jsTooltip" title="{lang}wcf.editor.button.media{/lang}"><span class="icon icon16 fa-file-o"></span></a></li>
		<li><a href="#" id="codemirror-content{@$languageID}-page" class="jsTooltip" title="{lang}wcf.editor.button.page{/lang}"><span class="icon icon16 fa-file-text-o"></span></a></li>
	</ul>
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Acp/Ui/CodeMirror/Media', 'WoltLabSuite/Core/Acp/Ui/CodeMirror/Page'], function(AcpUiCodeMirrorMedia, AcpUiCodeMirrorPage) {
			new AcpUiCodeMirrorMedia('content{@$languageID}');
			new AcpUiCodeMirrorPage('content{@$languageID}');
		});
	</script>
{/if}

{if $boxType == 'text'}
	<textarea name="content[{@$languageID}]" id="content{@$languageID}"
		{if $boxType == 'text'}
			class="wysiwygTextarea" data-disable-attachments="true" data-autosave="com.woltlab.wcf.box{$action|ucfirst}-{if $action == 'edit'}{@$boxID}{else}0{/if}-{@$languageID}"
			{if $action === 'edit'}data-autosave-last-edit-time="{@$box->lastUpdateTime}"{/if}
		{/if}
	>{if !$content[$languageID]|empty}{$content[$languageID]}{/if}</textarea>
	
	{include file='__wysiwygCmsToolbar'}
	{include file='wysiwyg' wysiwygSelector='content'|concat:$languageID}
{else}
	<div dir="ltr">
		<textarea name="content[{@$languageID}]" id="content{@$languageID}"
			{if $boxType == 'text'}
				class="wysiwygTextarea" data-disable-attachments="true" data-autosave="com.woltlab.wcf.box{$action|ucfirst}-{if $action == 'edit'}{@$boxID}{else}0{/if}-{@$languageID}"
				{if $action === 'edit'}data-autosave-last-edit-time="{@$box->lastUpdateTime}"{/if}
			{/if}
		>{if !$content[$languageID]|empty}{$content[$languageID]}{/if}</textarea>
	</div>
	{if $boxType == 'html'}
		{include file='codemirror' codemirrorMode='htmlmixed' codemirrorSelector='#content'|concat:$languageID}
	{elseif $boxType == 'tpl'}
		{include file='codemirror' codemirrorMode='smartymixed' codemirrorSelector='#content'|concat:$languageID}
	{/if}
{/if}
