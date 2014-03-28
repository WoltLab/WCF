$buttons.push('html');
$buttons.push('separator');

{if $__wcf->getBBCodeHandler()->isAvailableBBCode('b')}
	$buttons.push('bold');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('i')}
	$buttons.push('italic');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('u')}
	$buttons.push('underline');
{/if}

if ($buttons.length) {
	$buttons.push('separator');
}

{if $__wcf->getBBCodeHandler()->isAvailableBBCode('s')}
	$buttons.push('deleted');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('sub')}
	$buttons.push('subscript');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('sup')}
	$buttons.push('superscript');
{/if}

if ($buttons.length && $buttons[$buttons.length -1] != 'separator') {
	$buttons.push('separator');
}

{if $__wcf->getBBCodeHandler()->isAvailableBBCode('list')}
	$buttons.push('orderedlist');
	$buttons.push('unorderedlist');
	$buttons.push('outdent');
	$buttons.push('indent');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('align')}
	$buttons.push('alignment');
{/if}

if ($buttons.length && $buttons[$buttons.length -1] != 'separator') {
	$buttons.push('separator');
}

{* TODO
var $font = [ ];
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('font')}
	$font.push('Font');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('size')}
	$font.push('FontSize');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('color')}
	$font.push('TextColor');
{/if}
*}

{if $__wcf->getBBCodeHandler()->isAvailableBBCode('url')}
	$buttons.push('link');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('img')}
	$buttons.push('image');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('table')}
	$buttons.push('table');
{/if}

{if MODULE_SMILEY && (!$permissionCanUseSmilies|isset || $__wcf->getSession()->getPermission($permissionCanUseSmilies)) && $defaultSmilies|isset && $defaultSmilies|count}
	$buttons.push('smiley');
{/if}

if ($buttons.length && $buttons[$buttons.length -1] != 'separator') {
	$buttons.push('separator');
}
