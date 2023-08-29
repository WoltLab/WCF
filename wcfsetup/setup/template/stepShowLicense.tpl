{include file='header'}

<form method="post" action="install.php">
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.global.license{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.global.license.description{/lang}</p>
		</header>

		<dl{if $missingAcception|isset && $missingAcception} class="formError"{/if}>
			<dt>CKEditor 5 FREE FOR OPEN SOURCE LICENSE AGREEMENT</dt>
			<dd>
				<textarea rows="3" cols="40" readonly>THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL CKSOURCE OR ITS LICENSORS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.</textarea>
			</dd>
		</dl>
		
		<dl{if $missingAcception|isset && $missingAcception} class="formError"{/if}>
			<dt>WoltLab Suite™ Core</dt>
			<dd>
				<textarea rows="20" cols="40" readonly autofocus id="license">{$license}</textarea>
				<label><input type="checkbox" name="accepted" value="1"> {lang}wcf.global.license.accept.description{/lang}</label>
				{if $missingAcception|isset && $missingAcception}
					<small class="innerError">
						{lang}wcf.global.license.missingAcception{/lang}
					</small>
				{/if}
			</dd>
		</dl>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.next{/lang}" accesskey="s">
			<input type="hidden" name="send" value="1">
			<input type="hidden" name="step" value="{$nextStep}">
			<input type="hidden" name="tmpFilePrefix" value="{$tmpFilePrefix}">
			<input type="hidden" name="languageCode" value="{$languageCode}">
			<input type="hidden" name="dev" value="{$developerMode}">
		</div>
	</section>
</form>
<script>
if (typeof window._trackWcfSetupStep === 'function') window._trackWcfSetupStep('showLicense');
</script>
{include file='footer'}
