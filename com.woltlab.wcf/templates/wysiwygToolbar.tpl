buttonOptions = {
	alignment: { icon: 'align-left;false', title: '{jslang}wcf.editor.button.alignment{/jslang}' },
	bold: { icon: 'bold;false', title: '{jslang}wcf.editor.button.bold{/jslang}' },
	deleted: { icon: 'strikethrough;false', title: '{jslang}wcf.editor.button.strikethrough{/jslang}' },
	format: { icon: 'heading;false', title: '{jslang}wcf.editor.button.format{/jslang}' },
	html: { icon: 'code;false', title: '{jslang}wcf.editor.button.html{/jslang}' },
	italic: { icon: 'italic;false', title: '{jslang}wcf.editor.button.italic{/jslang}' },
	link: { icon: 'link;false', title: '{jslang}wcf.editor.button.link{/jslang}' },
	lists: { icon: 'list;false', title: '{jslang}wcf.editor.button.lists{/jslang}' },
	redo: { icon: 'arrow-rotate-right;false', title: '{jslang}wcf.editor.button.redo{/jslang}' },
	subscript: { icon: 'subscript;false', title: '{jslang}wcf.editor.button.subscript{/jslang}' },
	superscript: { icon: 'superscript;false', title: '{jslang}wcf.editor.button.superscript{/jslang}' },
	table: { icon: 'table;false', title: '{jslang}wcf.editor.button.table{/jslang}' },
	underline: { icon: 'underline;false', title: '{jslang}wcf.editor.button.underline{/jslang}' },
	undo: { icon: 'arrow-rotate-left;false', title: '{jslang}wcf.editor.button.undo{/jslang}' },
	woltlabColor: { icon: 'paintbrush;false', title: '{jslang}wcf.editor.button.color{/jslang}' },
	woltlabFont: { icon: 'font;false', title: '{jslang}wcf.editor.button.font{/jslang}' },
	woltlabFullscreen: { icon: 'expand;false', title: '{jslang}wcf.editor.button.fullscreen{/jslang}' },
	woltlabImage: { icon: 'image;false', title: '{jslang}wcf.editor.button.image{/jslang}' },
	woltlabMedia: { icon: 'file;false', title: '{jslang}wcf.editor.button.media{/jslang}' },
	woltlabQuote: { icon: 'quote-left;true', title: '{jslang}wcf.editor.button.quote{/jslang}' },
	woltlabSize: { icon: 'text-height;false', title: '{jslang}wcf.editor.button.size{/jslang}' }
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
