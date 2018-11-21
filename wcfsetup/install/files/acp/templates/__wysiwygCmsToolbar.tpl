{capture append='__redactorJavaScript'}, '{@$__wcf->getPath()}js/3rdParty/redactor2/plugins/WoltLabPage.js?v={@LAST_UPDATE_TIME}'{/capture}
{capture append='__redactorConfig'}
	buttonOptions.woltlabPage = { icon: 'fa-file-text-o', title: '{lang}wcf.editor.button.page{/lang}' };
	
	buttons.push('woltlabPage');
	
	config.plugins.push('WoltLabPage');
{/capture}
