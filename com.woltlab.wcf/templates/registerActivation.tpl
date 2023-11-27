{include file='authFlowHeader'}

{if $__wcf->user->userID && !$__wcf->user->isEmailConfirmed()}
	<woltlab-core-notice type="info">{lang}wcf.user.registerActivation.info{/lang}</woltlab-core-notice>
{/if}

{@$form->getHtml()}

{include file='authFlowFooter'}
