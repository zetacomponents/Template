<?php
/**
 * File containing the ezcTemplateDelimiterSourceToTstParser class
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
 * Parser for {delimiter}.
 *
 * @package Template
 * @version //autogen//
 * @access private
 */
class ezcTemplateDelimiterSourceToTstParser extends ezcTemplateSourceToTstParser
{
    /**
     * Passes control to parent.
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
                throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor,  ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_CURLY_BRACKET_CLOSE );
            }

            $cursor->advance();

            $el = new ezcTemplateDelimiterTstNode( $this->parser->source, $this->startCursor, $cursor );
            $el->isClosingBlock = true;
            $this->appendElement( $el );
            return true;
        }

        // handle opening block
        if ( $this->block->name == "delimiter" )
        {
            $delimiter = new ezcTemplateDelimiterTstNode( $this->parser->source, $this->startCursor, $cursor );
            $this->findNextElement();
            if ( $this->currentCursor->match( "modulo" ) )
            {
                $this->findNextElement();

                if ( !$this->parseOptionalType( 'Expression', null, false ) )
                {
                    throw new ezcTemplateSourceToTstParserException( $this, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_EXPRESSION );
                }

                if ( $this->lastParser->rootOperator instanceof ezcTemplateModifyingOperatorTstNode )
                {
                    throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_MODIFYING_EXPRESSION_NOT_ALLOWED );
                }

                $delimiter->modulo = $this->lastParser->rootOperator;

                if ( $this->currentCursor->match( "is" ) )
                {
                    $this->findNextElement();
                    if ( !$this->parseOptionalType( 'Expression', null, false ) )
                    {
                        throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_EXPRESSION );
                    }

                    if ( $this->lastParser->rootOperator instanceof ezcTemplateModifyingOperatorTstNode )
                    {
                        throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_MODIFYING_EXPRESSION_NOT_ALLOWED );
                    }

                    $delimiter->rest = $this->lastParser->rootOperator;
                    $this->findNextElement();
                }
                else
                {
                    $delimiter->rest = new ezcTemplateLiteralTstNode( $this->parser->source, $this->startCursor, $this->endCursor );
                    $delimiter->rest->value = 0;
                }
            }


            $this->appendElement( $delimiter );


            if ( !$this->parentParser->atEnd( $cursor, null, false ) )
            {
                throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor,
                    ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_CURLY_BRACKET_CLOSE );
            }

            $cursor->advance();
            return true;
        }
        elseif ( $this->block->name == "skip" )
        {
            $skip = new ezcTemplateLoopTstNode( $this->parser->source, $this->startCursor, $cursor, "skip" );

            $this->findNextElement();

            if ( !$this->parentParser->atEnd( $cursor, null, false ) )
            {
                throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_CURLY_BRACKET_CLOSE );
            }

            $cursor->advance();
            $this->appendElement( $skip );
            return true;
        }

        return false;
    }
}

?>
