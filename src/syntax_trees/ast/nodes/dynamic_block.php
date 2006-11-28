<?php
/**
 * File containing the ezcTemplateDynamicBlockAstNode class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @access private
 */
/**
 *
 * @package Template
 * @version //autogen//
 * @access private
 */

class ezcTemplateDynamicBlockAstNode extends ezcTemplateStatementAstNode
{
    public $escapeSingleQuote = false;

    public $body;

    /**
     * Initialize with function name code and optional arguments
     */
    public function __construct( ezcTemplateBodyAstNode $body = null )
    {
        parent::__construct();

        $this->body = $body;
    }

}

?>