buttonOptions = {
	alignment: { icon: 'fa-align-left', title: '{lang}wcf.editor.button.alignment{/lang}' },
	bold: { icon: 'fa-bold', title: '{lang}wcf.editor.button.bold{/lang}' },
	deleted: { icon: 'fa-strikethrough', title: '{lang}wcf.editor.button.strikethrough{/lang}' },
	format: { icon: 'fa-paragraph', title: '{lang}wcf.editor.button.format{/lang}' },
	html: { icon: 'fa-code', title: '{lang}wcf.editor.button.html{/lang}' },
	italic: { icon: 'fa-italic', title: '{lang}wcf.editor.button.italic{/lang}' },
	link: { icon: 'fa-link', title: '{lang}wcf.editor.button.link{/lang}' },
	lists: { icon: 'fa-list', title: '{lang}wcf.editor.button.lists{/lang}' },
	subscript: { icon: 'fa-subscript', title: '{lang}wcf.editor.button.subscript{/lang}' },
	superscript: { icon: 'fa-superscript', title: '{lang}wcf.editor.button.superscript{/lang}' },
	table: { icon: 'fa-table', title: '{lang}wcf.editor.button.table{/lang}' },
	underline: { icon: 'fa-underline', title: '{lang}wcf.editor.button.underline{/lang}' },
	woltlabColor: { icon: 'fa-paint-brush', title: '{lang}wcf.editor.button.color{/lang}' },
	woltlabImage: { icon: 'fa-picture-o', title: '{lang}wcf.editor.button.image{/lang}' },
	woltlabMedia: { icon: 'fa-file-o', title: '{lang}wcf.editor.button.media{/lang}' },
	woltlabQuote: { icon: 'fa-comment', title: '{lang}wcf.editor.button.quote{/lang}' },
	woltlabSize: { icon: 'fa-text-height', title: '{lang}wcf.editor.button.size{/lang}' }
};

buttons.push('html');

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
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('size')}
	buttons.push('woltlabSize');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('color')}
	buttons.push('woltlabColor');
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
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('table')}
	buttons.push('table');
{/if}

buttons.push('wcfSeparator');

buttons.push('woltlabMedia');
buttons.push('woltlabQuote');

{foreach from=$__wcf->getBBCodeHandler()->getButtonBBCodes(true) item=__bbcode}
	buttonOptions['{$__bbcode->bbcodeTag}'] = { icon: '{$__bbcode->wysiwygIcon}', title: '{lang}{$__bbcode->buttonLabel}{/lang}' };
	buttons.push('{$__bbcode->bbcodeTag}');
	customButtons.push('{$__bbcode->bbcodeTag}');
{/foreach}
