<?php
/**
 * File containing the ezcTemplateTranslationSourceToTstParser class
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
 * Parser for {tr} loop.
 *
 * @package Template
 * @version //autogen//
 * @access private
 */
class ezcTemplateTranslationSourceToTstParser extends ezcTemplateSourceToTstParser
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
        $el = new ezcTemplateTranslationTstNode( $this->parser->source, $this->startCursor, $cursor );

        $this->findNextElement();
        if ( !$this->parseRequiredType( 'String', null, false ) )
        {
            throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_STRING );
        }
        $el->string = $this->lastParser->element;

        // empty keys and values before the loop, so that multiple vars statements can work
        $currentKeys = $currentArray = array();
        $foundContext = $foundComment = false;
        $elementNr = 0;

        // the loop that parses context/comment/vars
        do
        {
            $foundSomething = false;
            $this->findNextElement();

            // Check if we have a context
            if ( $cursor->match ( 'context' ) )
            {
                if ( $foundContext )
                {
                    throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->lastCursor, ezcTemplateSourceToTstErrorMessages::MSG_CONTEXT_DUPLICATE );
                }
                $this->findNextElement();
                if ( !$this->parseRequiredType( 'String', null, false ) )
                {
                    throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_STRING );
                }
                $el->context = $this->lastParser->element;
                $foundSomething = $foundContext = true;
            }

            // Check if we have a comment
            if ( $cursor->match ( 'comment' ) )
            {
                if ( $foundComment )
                {
                    throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_COMMENT_DUPLICATE );
                }
                $this->findNextElement();
                if ( !$this->parseRequiredType( 'String', null, false ) )
                {
                    throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_STRING );
                }
                $el->comment = $this->lastParser->element;
                $foundSomething = $foundComment = true;
            }

            // Check the variables.
            if ( $cursor->match ( 'vars' ) )
            {
                do
                {
                    // save the cursor so that we can restore it to retry a different sequence
                    $saveCursor = clone $cursor;

                    // try to parse string => expression
                    if ( $elements = $this->parseSequence( array( array( 'type' => 'String' ), array( 'type' => 'Character', 'args' => '=>' ), array( 'type' => 'Expression' ) ) ) )
                    {
                        $currentKeys[$elementNr] = $elements[0];
                        $currentArray[$elementNr] = $elements[1];
                        $elementNr++;
                        continue;
                    }

                    // restore the cursor if not found
                    $this->currentCursor = $saveCursor;
                    $cursor = $saveCursor;

                    // try to parse integer => expression
                    if ( $elements = $this->parseSequence( array( array( 'type' => 'Integer' ), array( 'type' => 'Character', 'args' => '=>' ), array( 'type' => 'Expression' ) ) ) )
                    {
                        $currentKeys[$elementNr] = $elements[0];
                        $currentArray[$elementNr] = $elements[1];
                        $elementNr++;
                        continue;
                    }

                    // restore the cursor if not found
                    $this->currentCursor = $saveCursor;
                    $cursor = $saveCursor;

                    if ( !$this->parseRequiredType( 'Expression', null, false ) )
                    {
                        throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, "Expecting a valid variable definition (String => Expression; Integer => Expression; or Expression)." );
                    }
                    $expression = $this->lastParser->rootOperator;
                    unset( $currentKeys[$elementNr] );
                    $currentArray[$elementNr] = $expression;
                    $elementNr++;
                }
                while ( $cursor->match( ',' ) );

                $array = new ezcTemplateLiteralArrayTstNode( $this->parser->source, $this->startCursor, $cursor );
                $array->keys = $currentKeys;
                $array->value = $currentArray;

                $el->variables = $array;
                $foundSomething = true;
            }
        }
        while ( $foundSomething );

        if ( !$this->parentParser->atEnd( $cursor, null, false ) )
        {
            throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_CURLY_BRACKET_CLOSE );
        }

        $cursor->advance();

        $this->appendElement( $el );

        return true;
    }
}

?>
