{if $__wcf->session->getPermission('admin.content.cms.canUseMedia') || $__wcf->session->getPermission('admin.content.cms.canManageMedia')}
	{jsphrase name='wcf.media.chooseFile'}
	{jsphrase name='wcf.media.insert'}
	{jsphrase name='wcf.media.insert.imageSize'}
	{jsphrase name='wcf.media.insert.imageSize.small'}
	{jsphrase name='wcf.media.insert.imageSize.medium'}
	{jsphrase name='wcf.media.insert.imageSize.large'}
	{jsphrase name='wcf.media.insert.imageSize.original'}
	{jsphrase name='wcf.media.manager'}
	{jsphrase name='wcf.media.manager.dropZone'}
	{jsphrase name='wcf.media.manager.dropZone.select'}
	{jsphrase name='wcf.media.manager.selection.none'}
	{jsphrase name='wcf.media.button.select'}

	WoltLabLanguage.registerPhrase('wcf.media.manager.selection.multiple', '{jslang __literal=true}wcf.media.manager.selection.multiple{/jslang}');
{/if}
