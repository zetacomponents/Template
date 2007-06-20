<?php
/**
 * File containing the ezcTemplateOutdatedCompilationException class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005-2007 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * ezcTemplateOutdatedCompilationException is thrown internally to signal that
 * the current template is expired. 
 *
 * @package Template
 * @version //autogen//
 * @access private
 */
class ezcTemplateOutdatedCompilationException extends ezcTemplateException
{
    /**
     * Creates a exception for outdated compilations, error message is
     * specified by caller.
     *
     * @param string $msg
     */
    public function __construct( $msg )
    {
        parent::__construct( $msg );
    }
}
?>
