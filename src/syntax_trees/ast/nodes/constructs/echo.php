<?php
/**
 * File containing the ezcTemplateEchoAstNode class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @access private
 */
/**
 * Represents an echo construct.
 *
 * @package Template
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 * @access private
 */
class ezcTemplateEchoAstNode extends ezcTemplateStatementAstNode
{
    /**
     * List of code elements which are going to be evaluated and output.
     * @var array(ezcTemplateAstNode)
     */
    public $outputList;

    /**
     */
    public function __construct( Array $outputList = null )
    {
        parent::__construct();
        $this->outputList = array();

        if ( $outputList !== null )
        {
            foreach ( $outputList as $output )
            {
                $this->appendOutput( $output );
            }
        }
    }

    /**
     * Append a new output element to the current list.
     */
    public function appendOutput( ezcTemplateAstNode $output )
    {
        $this->outputList[] = $output;
    }

    /**
     * Returns the current list of output elements.
     * @return array(ezcTemplateAstNode)
     */
    public function getOutputList()
    {
        return $this->outputList;
    }

    /**
     * Validates the output parameters against their constraints.
     *
     * @throw Exception if the constraints are not met.
     * @todo Fix exception class
     */
    public function validate()
    {
        if ( count( $this->outputList ) == 0 )
        {
            throw new Exception( "Too few output parameters for class <" . get_class( $this ) . ">, needs at least 1 but got 0." );
        }
    }
}
?>
