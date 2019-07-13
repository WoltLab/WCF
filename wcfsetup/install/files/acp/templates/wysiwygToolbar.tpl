buttonOptions = {
	alignment: { icon: 'fa-align-left', title: '{lang}wcf.editor.button.alignment{/lang}' },
	bold: { icon: 'fa-bold', title: '{lang}wcf.editor.button.bold{/lang}' },
	deleted: { icon: 'fa-strikethrough', title: '{lang}wcf.editor.button.strikethrough{/lang}' },
	format: { icon: 'fa-header', title: '{lang}wcf.editor.button.format{/lang}' },
	html: { icon: 'fa-file-code-o', title: '{lang}wcf.editor.button.html{/lang}' },
	italic: { icon: 'fa-italic', title: '{lang}wcf.editor.button.italic{/lang}' },
	link: { icon: 'fa-link', title: '{lang}wcf.editor.button.link{/lang}' },
	lists: { icon: 'fa-list', title: '{lang}wcf.editor.button.lists{/lang}' },
	redo: { icon: 'fa-repeat', title: '{lang}wcf.editor.button.redo{/lang}' },
	subscript: { icon: 'fa-subscript', title: '{lang}wcf.editor.button.subscript{/lang}' },
	superscript: { icon: 'fa-superscript', title: '{lang}wcf.editor.button.superscript{/lang}' },
	table: { icon: 'fa-table', title: '{lang}wcf.editor.button.table{/lang}' },
	underline: { icon: 'fa-underline', title: '{lang}wcf.editor.button.underline{/lang}' },
	undo: { icon: 'fa-undo', title: '{lang}wcf.editor.button.undo{/lang}' },
	woltlabColor: { icon: 'fa-paint-brush', title: '{lang}wcf.editor.button.color{/lang}' },
	woltlabFont: { icon: 'fa-font', title: '{lang}wcf.editor.button.font{/lang}' },
	woltlabFullscreen: { icon: 'fa-expand', title: '{lang}wcf.editor.button.fullscreen{/lang}' },
	woltlabImage: { icon: 'fa-picture-o', title: '{lang}wcf.editor.button.image{/lang}' },
	woltlabMedia: { icon: 'fa-file-o', title: '{lang}wcf.editor.button.media{/lang}' },
	woltlabQuote: { icon: 'fa-comment', title: '{lang}wcf.editor.button.quote{/lang}' },
	woltlabSize: { icon: 'fa-text-height', title: '{lang}wcf.editor.button.size{/lang}' }
};

buttonMobile = ['format', 'bold', 'italic', 'underline', 'lists', 'link', 'woltlabImage'];

buttons.push('html');
buttons.push('undo');
buttons.push('redo');
buttons.push('woltlabFullscreen');

buttons.push('wcfSeparator');

buttons.push('format');

buttons.push('wcfSeparator');

buttons.push('bold');
buttons.push('italic');
buttons.push('underline');
buttons.push('deleted');

buttons.push('wcfSeparator');

buttons.push('subscript');
buttons.push('superscript');
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('font')}
	buttons.push('woltlabFont');
	allowedInlineStyles.push('font-family');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('size')}
	buttons.push('woltlabSize');
	allowedInlineStyles.push('font-size');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('color')}
	buttons.push('woltlabColor');
	allowedInlineStyles.push('color');
{/if}

buttons.push('wcfSeparator');

buttons.push('lists');
buttons.push('alignment');

{if $__wcf->getBBCodeHandler()->isAvailableBBCode('url')}
	buttons.push('link');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('img')}
	buttons.push('woltlabImage');
{/if}
buttons.push('table');

buttons.push('wcfSeparator');

{if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}
	buttons.push('woltlabMedia');
{/if}
buttons.push('woltlabQuote');

{foreach from=$__wcf->getBBCodeHandler()->getButtonBBCodes(true) item=__bbcode}
	{* the HTML bbcode must be handled differently, it conflicts with the `source` toggle-button *}
	{if $__bbcode->bbcodeTag === 'html'}
		buttonOptions['woltlabHtml'] = { icon: '{$__bbcode->wysiwygIcon}', title: '{$__bbcode->getButtonLabel()}' };
		buttons.push('woltlabHtml');
		customButtons.push('woltlabHtml');
	{else}
		buttonOptions['{$__bbcode->bbcodeTag}'] = { icon: '{$__bbcode->wysiwygIcon}', title: '{$__bbcode->getButtonLabel()}' };
		buttons.push('{$__bbcode->bbcodeTag}');
		customButtons.push('{$__bbcode->bbcodeTag}');
	{/if}
{/foreach}
