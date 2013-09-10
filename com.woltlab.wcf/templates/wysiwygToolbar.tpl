var $textStyles1 = [ ];
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('b')}
	$textStyles1.push('Bold');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('i')}
	$textStyles1.push('Italic');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('u')}
	$textStyles1.push('Underline');
{/if}

var $textStyles2 = [ ];
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('s')}
	$textStyles2.push('Strike');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('sub')}
	$textStyles2.push('Subscript');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('sup')}
	$textStyles2.push('Superscript');
{/if}

if ($textStyles2.length) {
	$textStyles1.push('-');
	$textStyles1 = $textStyles1.concat($textStyles2);
}

var $formatting = [ ];
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('list')}
	$formatting.push('NumberedList');
	$formatting.push('BulletedList');
	$formatting.push('-');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('align')}
	$formatting.push('JustifyLeft');
	$formatting.push('JustifyCenter');
	$formatting.push('JustifyRight');
	$formatting.push('JustifyBlock');
{/if}

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

var $other = [ ];
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('url')}
	$other.push('Link');
	$other.push('Unlink');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('img')}
	$other.push('Image');
{/if}
{if $__wcf->getBBCodeHandler()->isAvailableBBCode('table')}
	$other.push('Table');
{/if}
{if MODULE_SMILEY && (!$permissionCanUseSmilies|isset || $__wcf->getSession()->getPermission($permissionCanUseSmilies)) && $defaultSmilies|isset && $defaultSmilies|count}
	$other.push('Smiley');
{/if}

var __CKEDITOR_TOOLBAR = [ ];
__CKEDITOR_TOOLBAR.push(['Source', '-', 'Undo', 'Redo']);
if ($textStyles1.length) {
	__CKEDITOR_TOOLBAR.push($textStyles1);
}
if ($formatting.length) {
	__CKEDITOR_TOOLBAR.push($formatting);
}
if (__CKEDITOR_TOOLBAR.length > 1) {
	__CKEDITOR_TOOLBAR.push('/');
}
if ($font.length) {
	__CKEDITOR_TOOLBAR.push($font);
}
if ($other.length) {
	__CKEDITOR_TOOLBAR.push($other);
}
