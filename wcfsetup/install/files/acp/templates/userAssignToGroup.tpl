{include file='header'}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/usersL.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.user.assignToGroup{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="index.php?form=UserAssignToGroup">

	<div class="border content">
		
		<fieldset>
			<legend>{lang}wcf.acp.user.assignToGroup.markedUsers{/lang}</legend>
			
			<div>
				{implode from=$users item=$user}<a href="index.php?form=UserEdit&amp;userID={@$user->userID}{@SID_ARG_2ND}">{$user}</a>{/implode}
			</div>
		</fieldset>	
		
		<fieldset>
			<legend>{lang}wcf.acp.user.groups{/lang}</legend>
			
			<div><!-- ToDo: Definition List -->
				<div{if $errorField == 'groupIDs'} class="formError"{/if}>
					{htmlCheckboxes options=$groups name=groupIDs selected=$groupIDs}
					{if $errorField == 'groupIDs'}
						<small class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
						</small>
					{/if}
				</div>
			</div>
		</fieldset>
		
	</div>
	
	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 		<input type="hidden" name="userIDs" value="{@$userIDs}" />
 	</div>
</form>

{include file='footer'}
