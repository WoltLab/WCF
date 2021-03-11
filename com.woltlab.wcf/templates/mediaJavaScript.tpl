{if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}
	require(['Language', 'Permission'], function(Language, Permission) {
		Language.addObject({
			'wcf.global.button.insert': '{jslang}wcf.global.button.insert{/jslang}',
			'wcf.media.button.replaceFile': '{jslang}wcf.media.button.replaceFile{/jslang}',
			'wcf.media.button.select': '{jslang}wcf.media.button.select{/jslang}',
			'wcf.media.delete.confirmMessage': '{jslang __encode=true __literal=true}wcf.media.delete.confirmMessage{/jslang}',
			'wcf.media.insert': '{jslang}wcf.media.insert{/jslang}',
			'wcf.media.insert.imageSize': '{jslang}wcf.media.insert.imageSize{/jslang}',
			'wcf.media.insert.imageSize.small': '{jslang}wcf.media.insert.imageSize.small{/jslang}',
			'wcf.media.insert.imageSize.medium': '{jslang}wcf.media.insert.imageSize.medium{/jslang}',
			'wcf.media.insert.imageSize.large': '{jslang}wcf.media.insert.imageSize.large{/jslang}',
			'wcf.media.insert.imageSize.original': '{jslang}wcf.media.insert.imageSize.original{/jslang}',
			'wcf.media.manager': '{jslang}wcf.media.manager{/jslang}',
			'wcf.media.edit': '{jslang}wcf.media.edit{/jslang}',
			'wcf.media.button.insert': '{jslang}wcf.media.button.insert{/jslang}',
			'wcf.media.search.info.searchStringThreshold': '{jslang __literal=true}wcf.media.search.info.searchStringThreshold{/jslang}',
			'wcf.media.search.noResults': '{jslang}wcf.media.search.noResults{/jslang}',
			'wcf.media.upload.error.differentFileExtension': '{jslang}wcf.media.upload.error.differentFileExtension{/jslang}',
			'wcf.media.upload.error.differentFileType': '{jslang}wcf.media.upload.error.differentFileType{/jslang}',
			'wcf.media.upload.error.noImage': '{jslang}wcf.media.upload.error.noImage{/jslang}',
			'wcf.media.upload.error.uploadFailed': '{jslang}wcf.media.upload.error.uploadFailed{/jslang}',
			'wcf.media.upload.success': '{jslang}wcf.media.upload.success{/jslang}',
			'wcf.media.setCategory': '{jslang}wcf.media.setCategory{/jslang}'
		});
		
		Permission.add('admin.content.cms.canManageMedia', {if $__wcf->session->getPermission('admin.content.cms.canManageMedia')}true{else}false{/if});
	});
{/if}
