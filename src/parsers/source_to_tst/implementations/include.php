<?php
/**
 * File containing the ezcTemplateIncludeSourceToTstParser class
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @package Template
 * @version //autogen//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @access private
 */
/**
 *
 * @package Template
 * @version //autogen//
 * @access private
 */
class ezcTemplateIncludeSourceToTstParser extends ezcTemplateSourceToTstParser
{
    private ?ezcTemplateTstNode $value;
    private $element;

    /**
     * Passes control to parent.
     *
     * @param ezcTemplateParser $parser
     * @param ezcTemplateSourceToTstParser $parentParser
     * @param ezcTemplateCursor $startCursor
     */
    function __construct( ezcTemplateParser $parser, /*ezcTemplateSourceToTstParser*/ $parentParser, /*ezcTemplateCursor*/ $startCursor )
    {
        parent::__construct( $parser, $parentParser, $startCursor );
        $this->value = null;
        $this->element = null;
    }

    /**
     * Parses the current include expression.
     *
     * @param ezcTemplateCursor $cursor
     * @return bool
     */
    protected function parseCurrent( ezcTemplateCursor $cursor )
    {
        if ( $this->block->name == "include" )
        {
            return $this->parseInclude( $cursor );
        }
        elseif ( $this->block->name == "return" )
        {
            return $this->parseReturn( $cursor );
        }

        return false;
    }

    /**
     * Parses the current include.
     *
     * @param ezcTemplateCursor $cursor
     * @return bool
     */
    protected function parseInclude( ezcTemplateCursor $cursor )
    {
        $this->findNextElement();

        if ( !$this->parseOptionalType( "Expression", null, false ) )
        {
           throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_EXPRESSION );
        }

        if ( $this->lastParser->rootOperator instanceof ezcTemplateModifyingOperatorTstNode )
        {
            throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_MODIFYING_EXPRESSION_NOT_ALLOWED );
        }


        $include = new ezcTemplateIncludeTstNode( $this->parser->source, $this->startCursor, $this->currentCursor );
        $include->file = $this->lastParser->rootOperator;

        $this->findNextElement();

        if ( $this->currentCursor->match( 'send' ) )
        {
            $include->send = $this->parseExprAsVarArray( true );
        }

        if ( $this->currentCursor->match( 'receive' ) )
        {
            $include->receive = $this->parseVarAsVarArray( false );
        }


        if ( !$this->currentCursor->match( '}' ) )
        {
           throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_CURLY_BRACKET_CLOSE );
        }

        $this->appendElement( $include );
        return true;
    }

    /**
     * Parses the return.
     *
     * @param ezcTemplateCursor $cursor
     * @return bool
     */
    protected function parseReturn( ezcTemplateCursor $cursor )
    {
        $return = new ezcTemplateReturnTstNode( $this->parser->source, $this->startCursor, $this->currentCursor );

        $return->variables = $this->parseExprAsVarArray( true );

        if ( !$this->currentCursor->match( '}' ) )
        {
           throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_CURLY_BRACKET_CLOSE );
        }

        $this->appendElement( $return );
        return true;
    }

    /**
     * Parses '<expression> [ as <var> ]'
     *
     * @param bool $symbolCheck
     * @return bool
     */
    protected function parseExprAsVarArray( $symbolCheck )
    {
        $variables = array();

        do
        {
            $this->findNextElement();

            if ( $this->parseOptionalType( "Expression", null, false ) )
            {
                if ( $this->lastParser->rootOperator instanceof ezcTemplateModifyingOperatorTstNode )
                {
                    throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_MODIFYING_EXPRESSION_NOT_ALLOWED );
                }

                if ( $this->lastParser->rootOperator instanceof ezcTemplateVariableTstNode )
                {
                    $asOptional = true;
                }
                else
                {
                    $asOptional = false;
                }

                // $asOptional = false;
                $lastVal = $this->lastParser->rootOperator;
                $this->findNextElement();
            }
            else
            {
                throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_VARIABLE );
            }

            if ( $this->currentCursor->match( 'as' ) )
            {
                $expr = $lastVal;
                $this->findNextElement();

                if ( !$this->parseOptionalType( "Variable", null, false ) )
                {
                    throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_VARIABLE );
                }

                $variables[ $this->lastParser->element->name ] = $expr ;
            }
            else
            {
                if ( !$asOptional )
                {
                    throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_AS );
                }

                $variables[ $lastVal->name ] = null;
            }



            $this->findNextElement();
        }
        while ( $this->currentCursor->match( ',' ) );


        return $variables;
    }

    /**
     * Parses '<var> [ as <var> ]'
     *
     * @param bool $symbolCheck
     * @return bool
     */
    protected function parseVarAsVarArray( $symbolCheck )
    {
        $variables = array();

        do
        {
            $this->findNextElement();

            if ( !$this->parseOptionalType( "Variable", null, false ) )
            {
               throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_VARIABLE );
            }

            if ( $symbolCheck )
            {
                if ( !$this->parser->symbolTable->retrieve( $this->lastParser->element->name ) )
                {
                    throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, $this->parser->symbolTable->getErrormessage() );
                }
            }
            else
            {
                // Do not overwrite the type of the existing variable.
                if ( $this->parser->symbolTable->retrieve( $this->lastParser->element->name ) === false )
                {
                    $this->parser->symbolTable->enter( $this->lastParser->element->name, ezcTemplateSymbolTable::VARIABLE );
                }
            }

            $this->findNextElement();

            if ( $this->currentCursor->match( 'as' ) )
            {
                $oldName = $this->lastParser->element->name;
                $this->findNextElement();

                if ( !$this->parseOptionalType( "Variable", null, false ) )
                {
                   throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_VARIABLE );
                }

                if ( $this->parser->symbolTable->retrieve( $this->lastParser->element->name ) === false )
                {
                    $this->parser->symbolTable->enter( $this->lastParser->element->name, ezcTemplateSymbolTable::VARIABLE );
                }

                $variables[ $oldName ] = $this->lastParser->element->name;
            }
            else
            {
                $variables[] = $this->lastParser->element->name;
            }

        }
        while ( $this->currentCursor->match( ',' ) );

        return $variables;
    }

    /**
     * Returns true if the current character is a curly bracket (}) which means
     * the end of the block.
     *
     * @param ezcTemplateCursor $cursor
     * @param ezcTemplateTstNode $operator
     * @param bool $finalize
     * @return bool
     *
     * @todo Can be removed?
     */
    public function atEnd( ezcTemplateCursor $cursor, /*ezcTemplateTstNode*/ $operator, $finalize = true )
    {
        return ( $cursor->current( 1 ) == "}"  || $cursor->current( 1 ) == "," );
    }
}

?>
