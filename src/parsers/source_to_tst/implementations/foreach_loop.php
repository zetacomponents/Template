<?php
/**
 * File containing the ezcTemplateForeachLoopSourceToTstParser class
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
 * Parser for {foreach} loop.
 *
 * @package Template
 * @version //autogen//
 * @access private
 */
class ezcTemplateForeachLoopSourceToTstParser extends ezcTemplateSourceToTstParser
{
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
        $this->block = null;
    }

    /**
     * Parses the expression by using the ezcTemplateExpressionSourceToTstParser class.
     *
     * @param ezcTemplateCursor $cursor
     * @return bool
     */
    protected function parseCurrent( ezcTemplateCursor $cursor )
    {
        // handle closing block
        if ( $this->block->isClosingBlock )
        {
            $this->findNextElement();
            if ( !$this->parentParser->atEnd( $cursor, null, false ) )
            {
                throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_CURLY_BRACKET_CLOSE );
            }

            $cursor->advance();

            $el = new ezcTemplateForeachLoopTstNode( $this->parser->source, $this->startCursor, $cursor );
            $el->isClosingBlock = true;
            $this->appendElement( $el );
            return true;
        }

        // handle opening block
        $el = new ezcTemplateForeachLoopTstNode( $this->parser->source, $this->startCursor, $cursor );

        $this->findNextElement();
        if ( !$this->parseOptionalType( 'Expression', null, false ) )
        {
            throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_EXPRESSION );
        }

        if ( $this->lastParser->rootOperator instanceof ezcTemplateModifyingOperatorTstNode )
        {
            throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_MODIFYING_EXPRESSION_NOT_ALLOWED );
        }

        $el->array = $this->lastParser->rootOperator;

        $this->findNextElement();
        if ( !$this->currentCursor->match( 'as' ) )
        {
            throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_AS );
        }
        $this->findNextElement();
        if ( !$this->parseRequiredType( 'Variable', null, false ) )
        {
            throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_VARIABLE );
        }
        $el->itemVariableName = $this->lastParser->element->name;

        $this->findNextElement();

        $canBeArrow = true;

        // parse "=> $itemVar" clause if we're not at the end yet
        if ( $cursor->match ( '=>' ) )
        {
            $canBeArrow = false;
            $this->findNextElement();

            // parse item variable
            if ( !$this->parseRequiredType( 'Variable', null, false ) )
            {
                throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_VARIABLE );
            }

            // skip whitespace and comments before the closing brace
            $this->findNextElement();
            $el->keyVariableName  = $el->itemVariableName;

            // Key Look up in the symbol table.
            if ( !$this->parser->symbolTable->enter( $el->keyVariableName, ezcTemplateSymbolTable::VARIABLE, true ) )
            {
                throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, $this->parser->symbolTable->getErrorMessage() );
            }

            $el->itemVariableName = $this->lastParser->variableName;
        }

        // Value lookup in the symbol table.
        if ( !$this->parser->symbolTable->enter( $el->itemVariableName, ezcTemplateSymbolTable::VARIABLE, true ) )
        {
            throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, $this->parser->symbolTable->getErrorMessage() );
        }

        // Check the cycle.
        while ( ( $matchIncrement = $cursor->match ( 'increment' ) ) || $cursor->match ( 'decrement' ) )
        {
            $canBeArrow = false;

            do
            {
                $this->findNextElement();
                if ( !$this->parseOptionalType( 'Variable', null, false ) )
                {
                    throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_VARIABLE );
                }

                if ( $matchIncrement )
                {
                    $el->increment[] = $this->lastParser->element;
                }
                else
                {
                    $el->decrement[] = $this->lastParser->element;
                }

                $this->findNextElement();
            }
            while ( $cursor->match( "," ) );
        }

        // Check the offset.
        if ( $cursor->match ( 'offset' ) )
        {
            $canBeArrow = false;
            $this->findNextElement();
            if ( !$this->parseOptionalType( 'Expression', null, false ) )
            {
                throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_EXPRESSION );
            }

            if ( $this->lastParser->rootOperator instanceof ezcTemplateModifyingOperatorTstNode )
            {
                throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_MODIFYING_EXPRESSION_NOT_ALLOWED );
            }

            $el->offset = $this->lastParser->rootOperator;
            $this->findNextElement();
        }

        // check for 'limit'.
        if ( $cursor->match ( 'limit' ) )
        {
            $canBeArrow = false;
            $this->findNextElement();
            if ( !$this->parseOptionalType( 'Expression', null, false ) )
            {
                throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_EXPRESSION );
            }

            if ( $this->lastParser->rootOperator instanceof ezcTemplateModifyingOperatorTstNode )
            {
                throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_MODIFYING_EXPRESSION_NOT_ALLOWED );
            }


            $el->limit = $this->lastParser->rootOperator;
            $this->findNextElement();
        }


        if ( !$this->parentParser->atEnd( $cursor, null, false ) )
        {
            throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor,
                $canBeArrow ?  ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_ARROW_OR_CLOSE_CURLY_BRACKET :
                               ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_CURLY_BRACKET_CLOSE  );
        }

        $cursor->advance();

        $this->appendElement( $el );

        return true;
    }
}

?>
