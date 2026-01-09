{if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}
	{jsphrase name='wcf.global.button.insert'}
	{jsphrase name='wcf.media.button.replaceFile'}
	{jsphrase name='wcf.media.button.select'}
	{jsphrase name='wcf.media.insert'}
	{jsphrase name='wcf.media.insert.imageSize'}
	{jsphrase name='wcf.media.insert.imageSize.small'}
	{jsphrase name='wcf.media.insert.imageSize.medium'}
	{jsphrase name='wcf.media.insert.imageSize.large'}
	{jsphrase name='wcf.media.insert.imageSize.original'}
	{jsphrase name='wcf.media.manager'}
	{jsphrase name='wcf.media.edit'}
	{jsphrase name='wcf.media.button.insert'}
	{jsphrase name='wcf.media.search.noResults'}
	{jsphrase name='wcf.media.upload.error.differentFileExtension'}
	{jsphrase name='wcf.media.upload.error.differentFileType'}
	{jsphrase name='wcf.media.upload.error.noImage'}
	{jsphrase name='wcf.media.upload.error.uploadFailed'}
	{jsphrase name='wcf.media.upload.success'}
	{jsphrase name='wcf.media.setCategory'}

	require(['Language', 'Permission'], function(Language, Permission) {
		Language.addObject({
			'wcf.media.delete.confirmMessage': '{jslang __encode=true __literal=true}wcf.media.delete.confirmMessage{/jslang}',
			'wcf.media.search.info.searchStringThreshold': '{jslang __literal=true}wcf.media.search.info.searchStringThreshold{/jslang}',
		});
		
		Permission.add('admin.content.cms.canManageMedia', {if $__wcf->session->getPermission('admin.content.cms.canManageMedia')}true{else}false{/if});
	});
{/if}
