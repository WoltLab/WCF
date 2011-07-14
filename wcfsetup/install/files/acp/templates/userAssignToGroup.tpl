{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/usersL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.user.assignToGroup{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="index.php?form=UserAssignToGroup">

	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.user.assignToGroup.markedUsers{/lang}</legend>
				
				<div>
					{implode from=$users item=$user}<a href="index.php?form=UserEdit&amp;userID={@$user->userID}{@SID_ARG_2ND}">{$user}</a>{/implode}
				</div>
			</fieldset>	
			
			<fieldset>
				<legend>{lang}wcf.acp.user.groups{/lang}</legend>
				
				<div>
					<div class="formField{if $errorField == 'groupIDs'} formError{/if}">
						{htmlCheckboxes options=$groups name=groupIDs selected=$groupIDs}
						{if $errorField == 'groupIDs'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		{@SID_INPUT_TAG}
 		<input type="hidden" name="userIDs" value="{@$userIDs}" />
 	</div>
</form>

{include file='footer'}