<?php
namespace wcf\system\html\input\filter;

/**
 * TOOD documentation
 * @since	3.0
 */
interface IHtmlInputFilter {
	/**
	 * Applies filters on unsafe html.
	 * 
	 * @param	string		$html		unsafe html
	 * @return	string		filtered html
	 */
	public function apply($html);
}
