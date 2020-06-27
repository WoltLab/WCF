{if $__wcf->session->getPermission('admin.content.cms.canUseMedia')}
	require(['Language', 'Permission'], function(Language, Permission) {
		Language.addObject({
			'wcf.global.button.insert': '{lang}wcf.global.button.insert{/lang}',
			'wcf.media.button.replaceFile': '{lang}wcf.media.button.replaceFile{/lang}',
			'wcf.media.button.select': '{lang}wcf.media.button.select{/lang}',
			'wcf.media.delete.confirmMessage': '{lang __encode=true __literal=true}wcf.media.delete.confirmMessage{/lang}',
			'wcf.media.imageDimensions.value': '{lang __literal=true}wcf.media.imageDimensions.value{/lang}',
			'wcf.media.insert': '{lang}wcf.media.insert{/lang}',
			'wcf.media.insert.imageSize': '{lang}wcf.media.insert.imageSize{/lang}',
			'wcf.media.insert.imageSize.small': '{lang}wcf.media.insert.imageSize.small{/lang}',
			'wcf.media.insert.imageSize.medium': '{lang}wcf.media.insert.imageSize.medium{/lang}',
			'wcf.media.insert.imageSize.large': '{lang}wcf.media.insert.imageSize.large{/lang}',
			'wcf.media.insert.imageSize.original': '{lang}wcf.media.insert.imageSize.original{/lang}',
			'wcf.media.manager': '{lang}wcf.media.manager{/lang}',
			'wcf.media.edit': '{lang}wcf.media.edit{/lang}',
			'wcf.media.button.insert': '{lang}wcf.media.button.insert{/lang}',
			'wcf.media.search.info.searchStringThreshold': '{lang __literal=true}wcf.media.search.info.searchStringThreshold{/lang}',
			'wcf.media.search.noResults': '{lang}wcf.media.search.noResults{/lang}',
			'wcf.media.upload.error.differentFileExtension': '{lang}wcf.media.upload.error.differentFileExtension{/lang}',
			'wcf.media.upload.error.differentFileType': '{lang}wcf.media.upload.error.differentFileType{/lang}',
			'wcf.media.upload.error.noImage': '{lang}wcf.media.upload.error.noImage{/lang}',
			'wcf.media.upload.error.uploadFailed': '{lang}wcf.media.upload.error.uploadFailed{/lang}',
			'wcf.media.upload.success': '{lang}wcf.media.upload.success{/lang}',
			'wcf.media.setCategory': '{lang}wcf.media.setCategory{/lang}'
		});
		
		Permission.add('admin.content.cms.canManageMedia', {if $__wcf->session->getPermission('admin.content.cms.canManageMedia')}true{else}false{/if});
	});
{/if}
