<?php
/**
 * File containing the ezcTemplateCacheSourceToTstParser class
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
 * Parser for custom template blocks following a generic syntax.
 *
 * @package Template
 * @version //autogen//
 * @access private
 */
class ezcTemplateCacheSourceToTstParser extends ezcTemplateSourceToTstParser
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
        // Disable caching.
        // return false;

        if ( $cursor->match( "dynamic" ) )
        {
            $cacheNode = new ezcTemplateDynamicBlockTstNode( $this->parser->source, $this->startCursor, $cursor );

            if ( $this->block->isClosingBlock )
            {
                $cacheNode->isClosingBlock = true;
            }

            $this->appendElement( $cacheNode);
            $this->findNextElement( $cursor );

            if ( !$cursor->match( "}" ) )
            {
                throw new ezcTemplateParserException( $this->parser->source, $cursor, $cursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_CURLY_BRACKET_CLOSE );
            }

            return true;
        }

        $cacheNode = null;
        if ( $cursor->match( "cache_template" ) )
        {
            $this->parser->hasCacheBlocks = true;
            $cacheNode = new ezcTemplateCacheTstNode( $this->parser->source, $this->startCursor, $cursor );
            $cacheNode->type = ezcTemplateCacheTstNode::TYPE_CACHE_TEMPLATE;
        }
        elseif ($cursor->match( "cache_block" ) )
        {
            $this->parser->hasCacheBlocks = true;
            $cacheNode = new ezcTemplateCacheBlockTstNode( $this->parser->source, $this->startCursor, $cursor );
            // $cacheNode->type = ezcTemplateCacheTstNode::TYPE_CACHE_BLOCK;

            if ( $this->block->isClosingBlock )
            {
                $cacheNode->isClosingBlock = true; // Set closing block.

                $this->appendElement( $cacheNode );
                $this->findNextElement( $cursor );

                if ( !$cursor->match( "}" ) )
                {
                    throw new ezcTemplateParserException( $this->parser->source, $cursor, $cursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_CURLY_BRACKET_CLOSE );
                }

                return true;
            }
        }
        else
        {
            return false;
        }


        // We do have an opening cache_block or cache_template.
        $this->findNextElement( $cursor );

        while ( $matches = $cursor->pregMatchComplete( "#^([a-zA-Z_][a-zA-Z0-9_-]*)(?:[^a-zA-Z])#i" ) )
        {
            $name = $matches[1][0];
            $cursor->advance( strlen( $name ) );
            $this->findNextElement( $cursor );

            if ( $name == "keys" )
            {
                do
                {
                    $this->findNextElement( $cursor );

                    if ( ! $this->parseOptionalType( "Expression", $this->currentCursor, false ) )
                    {
                        throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_VARIABLE );
                    }
                    $cacheNode->keys[] = $this->lastParser->children[0];
                    $this->findNextElement( $cursor );
                }
                while ( $cursor->match(",") );

                // $this->parser->template->configuration->cacheSystem->appendCacheKeys( $values );
            }
            elseif ( $name == "ttl" )
            {
                // The parameter has an expression.
                if ( !$this->parseOptionalType( 'Expression', null, false ) )
                {
                    throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_EXPRESSION );
                }

                if ( $this->lastParser->rootOperator instanceof ezcTemplateModifyingOperatorTstNode )
                {
                    throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, ezcTemplateSourceToTstErrorMessages::MSG_MODIFYING_EXPRESSION_NOT_ALLOWED );
                }

                // Append the parameter to the "namedParameters" array.
                $cacheNode->ttl = $this->lastParser->rootOperator;
            }
            else
            {
                throw new ezcTemplateParserException( $this->parser->source, $this->startCursor, $this->currentCursor, "Unknown keyword: " . $name );
            }
        }

        $this->appendElement( $cacheNode);
        $this->findNextElement( $cursor );

        if ( !$cursor->match( "}" ) )
        {
            throw new ezcTemplateParserException( $this->parser->source, $cursor, $cursor, ezcTemplateSourceToTstErrorMessages::MSG_EXPECT_CURLY_BRACKET_CLOSE );
        }

        return true;
    }

}

?>
