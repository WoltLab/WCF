<?php
// @codingStandardsIgnoreFile
/**
 * Steam direct join protocol
 */
class HTMLPurifier_URIScheme_steam extends HTMLPurifier_URIScheme
{
    /**
     * @param HTMLPurifier_URI $uri
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool
     */
    public function doValidate(&$uri, $config, $context)
    {
        $uri->userinfo = null;
        
        return true;
    }
}

// vim: et sw=4 sts=4
