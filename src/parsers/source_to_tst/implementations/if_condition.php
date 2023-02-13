<?php
/**
 * File containing the ezcTemplateIfConditionSourceToTstParser class
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
 * Parser for {if} control structure.
 *
 * Parses inside the blocks {...} and looks for an expression by using the
 * ezcTemplateExpressionSourceToTstParser class.
 *
 * @package Template
 * @version //autogen//
 * @access private
 */
class ezcTemplateIfConditionSourceToTstParser extends ezcTemplateSourceToTstParser
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
        $name = $this->block->name;

        // handle closing block
        if ( $this->block->isClosingBlock )
        {
            // skip whitespace and comments
            $this->findNextElement();

            if ( !$cursor->match( '}' ) )
            {
                throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_CURLY_BRACKET_CLOSE );
            }

            $el = new ezcTemplateIfConditionTstNode( $this->parser->source, $this->startCursor, $cursor );
            $el->name = 'if';
            $el->isClosingBlock = true;
            $this->appendElement( $el );
            return true;
        }

        $condition = null;

        $this->findNextElement();

        if ( $name != 'else' ) // Parse condition
        {
            if ( !$this->parseRequiredType( 'Expression', null, false ) )
            {
                throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_EXPRESSION );
            }

            $condition = $this->lastParser->rootOperator;
            if ( $condition instanceof ezcTemplateModifyingOperatorTstNode )
            {
                throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_MODIFYING_EXPRESSION_NOT_ALLOWED );
            }

            $this->findNextElement();
        }

        if ( !$cursor->match( '}' ) )
        {
            throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_CURLY_BRACKET_CLOSE );
        }

        $cb = new ezcTemplateConditionBodyTstNode( $this->parser->source, $this->startCursor, $cursor );
        $cb->condition = $condition;
        $cb->name = $name;

        if ( $name == 'if' )
        {
            $el = new ezcTemplateIfConditionTstNode( $this->parser->source, $this->startCursor, $cursor );
            $el->children[] = $cb;
            $el->name = 'if';
            $this->appendElement( $el );
        }
        else
        {
            $this->appendElement( $cb );
        }

        return true;
    }
}

?>
