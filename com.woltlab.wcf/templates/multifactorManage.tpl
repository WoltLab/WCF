{capture assign='pageTitle'}{lang}wcf.user.security.multifactor.{$method->objectType}.manage{/lang}{/capture}
{capture assign='contentTitle'}{lang}wcf.user.security.multifactor.{$method->objectType}.manage{/lang}{/capture}

{include file='userMenuSidebar'}

{include file='header' __disableAds=true __sidebarLeftHasMenu=true}

{if $backupForm}
	{if $form->showsSuccessMessage()}
		<woltlab-core-notice type="success">{unsafe:$form->getSuccessMessage()}</woltlab-core-notice>
	{/if}
	
	{unsafe:$backupForm->getNodeById('existingCodesContainer')->getHtml()}
{else}
	{unsafe:$form->getHtml()}
{/if}

{include file='footer' __disableAds=true}
