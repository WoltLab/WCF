buttonOptions = {
	alignment: { icon: 'fa-align-left', title: '{jslang}wcf.editor.button.alignment{/jslang}' },
	bold: { icon: 'fa-bold', title: '{jslang}wcf.editor.button.bold{/jslang}' },
	deleted: { icon: 'fa-strikethrough', title: '{jslang}wcf.editor.button.strikethrough{/jslang}' },
	format: { icon: 'fa-header', title: '{jslang}wcf.editor.button.format{/jslang}' },
	html: { icon: 'fa-file-code-o', title: '{jslang}wcf.editor.button.html{/jslang}' },
	italic: { icon: 'fa-italic', title: '{jslang}wcf.editor.button.italic{/jslang}' },
	link: { icon: 'fa-link', title: '{jslang}wcf.editor.button.link{/jslang}' },
	lists: { icon: 'fa-list', title: '{jslang}wcf.editor.button.lists{/jslang}' },
	redo: { icon: 'fa-repeat', title: '{jslang}wcf.editor.button.redo{/jslang}' },
	subscript: { icon: 'fa-subscript', title: '{jslang}wcf.editor.button.subscript{/jslang}' },
	superscript: { icon: 'fa-superscript', title: '{jslang}wcf.editor.button.superscript{/jslang}' },
	table: { icon: 'fa-table', title: '{jslang}wcf.editor.button.table{/jslang}' },
	underline: { icon: 'fa-underline', title: '{jslang}wcf.editor.button.underline{/jslang}' },
	undo: { icon: 'fa-undo', title: '{jslang}wcf.editor.button.undo{/jslang}' },
	woltlabColor: { icon: 'fa-paint-brush', title: '{jslang}wcf.editor.button.color{/jslang}' },
	woltlabFont: { icon: 'fa-font', title: '{jslang}wcf.editor.button.font{/jslang}' },
	woltlabFullscreen: { icon: 'fa-expand', title: '{jslang}wcf.editor.button.fullscreen{/jslang}' },
	woltlabImage: { icon: 'fa-picture-o', title: '{jslang}wcf.editor.button.image{/jslang}' },
	woltlabMedia: { icon: 'fa-file-o', title: '{jslang}wcf.editor.button.media{/jslang}' },
	woltlabQuote: { icon: 'fa-comment', title: '{jslang}wcf.editor.button.quote{/jslang}' },
	woltlabSize: { icon: 'fa-text-height', title: '{jslang}wcf.editor.button.size{/jslang}' }
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
