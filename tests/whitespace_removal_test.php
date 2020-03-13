<?php
/**
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
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @version //autogentag//
 * @filesource
 * @package Template
 * @subpackage Tests
 */

/**
 * @package Template
 * @subpackage Tests
 */
class ezcTemplateWhitespaceRemovalTest extends ezcTestCase
{
    public static function suite()
    {
         return new PHPUnit\Framework\TestSuite( "ezcTemplateWhitespaceRemovalTest" );
    }

    /**
     * Test if trimming a string containing only whitespace returns an empty string
     * for both leading and trailing trimming.
     */
    public function testWhitespaceOnlyStringIsTrimmed()
    {
        $ws = new ezcTemplateWhitespaceRemoval();

        // Contains all whitespace characters
        $lines = array( array( "    ", "\n" ),
                        array( "\t", "\r" ),
                        array( "\x0B", false ) );

        $leading = array( array( "", false ),
                          array( "", false ),
                          array( "", false ) );

        $trailing = array( array( "", "\n" ),
                           array( "", false ),
                           array( "", false ) );

        self::assertSame( $leading, $ws->trimLeading( $lines ), 'Trimming leading WS does not return an empty string.' );
        self::assertSame( $trailing, $ws->trimTrailing( $lines ), 'Trimming trailing WS does not return an empty string.' );
    }

    /**
     * Test if trimming an empty stringreturns an empty string
     * for both leading and trailing trimming.
     */
    public function testEmptyStringIsTrimmed()
    {
        $ws = new ezcTemplateWhitespaceRemoval();

        // Empty string
        $lines = array( array( "", false ) );
        $empty = array( array( "", false ) );

        self::assertSame( $empty, $ws->trimLeading( $lines ), 'Trimming leading WS does not return an empty string.' );
        self::assertSame( $empty, $ws->trimTrailing( $lines ), 'Trimming trailing WS does not return an empty string.' );
    }

    /**
     * Test if trimming a string containing trailing whitespace is only
     * trimmed at the trailing lines and not the leading ones
     */
    public function testTextWithTrailingWhitespaceIsTrimmed()
    {
        $ws = new ezcTemplateWhitespaceRemoval();

        // Contains text with all whitespace characters at the end
        $lines = array( array( "a simple line", "\n" ),
                        array( "and a second one with whitespace to keep   ", "\n" ),
                        array( "\t", "\r" ),
                        array( "\x0B", false ) );

        // No lines are changed so it should return false
        $leading = false;

        $trailing = array( array( "a simple line", "\n" ),
                           array( "and a second one with whitespace to keep   ", "\n" ),
                           array( "", false ),
                           array( "", false ) );

        self::assertSame( $leading, $ws->trimLeading( $lines ), 'String without leading WS should not be trimmed.' );
        self::assertSame( $trailing, $ws->trimTrailing( $lines ), 'String with trailing WS should be trimmed.' );
    }

    /**
     * Test if trimming a string containing leading whitespace is only
     * trimmed at the leading lines and not the trailing ones
     */
    public function testTextWithLeadingWhitespaceIsTrimmed()
    {
        $ws = new ezcTemplateWhitespaceRemoval();

        // Contains text with all whitespace characters at the start
        $lines = array( array( "", "\n" ),
                        array( "\t", "\r" ),
                        array( "\x0B", "\n" ),
                        array( "a simple line", "\n" ),
                        array( "and a second one with whitespace to keep   ", false ) );

        $leading = array( array( "", false ),
                          array( "", false ),
                          array( "", false ),
                          array( "a simple line", "\n" ),
                          array( "and a second one with whitespace to keep   ", false ) );

        // No lines are changed so it should return false
        $trailing = false;

        self::assertSame( $leading, $ws->trimLeading( $lines ), 'String with leading WS should be trimmed.' );
        self::assertSame( $trailing, $ws->trimTrailing( $lines ), 'String without trailing WS should not be trimmed.' );
    }

    /**
     * Test if trimming indentations with tabs are handled properly.
     */
    public function testLineWithTabsIsTrimmedCorrectly()
    {
        $ws = new ezcTemplateWhitespaceRemoval();
        $ws->tabSize = 8;

        for ( $i = 0; $i <= 8; ++$i )
        {
            self::assertSame( "", $ws->trimIndentationLine( "", $i ),
                              "Empty string trimmed at indentation {$i} should be empty." );
        }

        self::assertSame( "\t",   $ws->trimIndentationLine( "\t", 0 ),
                          "String with tab trimmed at indentation 0 should have the tab marker." );
        self::assertSame( "", $ws->trimIndentationLine( "\t", 1 ),
                          "String with tab trimmed at indentation 1 should be empty." );
        self::assertSame( "", $ws->trimIndentationLine( "\t", 7 ),
                          "String with tab trimmed at indentation 7 should be empty." );
        self::assertSame( "", $ws->trimIndentationLine( "\t", 8 ),
                          "String with tab trimmed at indentation 8 should be empty." );

        self::assertSame( " \t",   $ws->trimIndentationLine( " \t", 0 ),
                          "String with space+tab trimmed at indentation 0 should have the space+tab marker." );
        self::assertSame( "\t", $ws->trimIndentationLine( " \t", 1 ),
                          "String with space+tab trimmed at indentation 1 should have the tab marker." );
        self::assertSame( "", $ws->trimIndentationLine( " \t", 2 ),
                          "String with space+tab trimmed at indentation 2 should be empty." );
        self::assertSame( "", $ws->trimIndentationLine( " \t", 7 ),
                          "String with space+tab trimmed at indentation 7 should be empty." );
        self::assertSame( "", $ws->trimIndentationLine( " \t", 8 ),
                          "String with space+tab trimmed at indentation 8 should be empty." );

        self::assertSame( "       \t", $ws->trimIndentationLine( "       \t", 0 ),
                          "String with 7*space+tab trimmed at indentation 0 should have the 7*space+tab marker." );
        self::assertSame( "      \t",  $ws->trimIndentationLine( "       \t", 1 ),
                          "String with 7*space+tab trimmed at indentation 1 should have the 6*space+tab marker." );
        self::assertSame( "     \t",   $ws->trimIndentationLine( "       \t", 2 ),
                          "String with 7*space+tab trimmed at indentation 2 should have the 5*space+tab marker." );
        self::assertSame( "    \t",    $ws->trimIndentationLine( "       \t", 3 ),
                          "String with 7*space+tab trimmed at indentation 3 should have the 4*space+tab marker." );
        self::assertSame( "   \t",     $ws->trimIndentationLine( "       \t", 4 ),
                          "String with 7*space+tab trimmed at indentation 4 should have the 3*space+tab marker." );
        self::assertSame( "  \t",      $ws->trimIndentationLine( "       \t", 5 ),
                          "String with 7*space+tab trimmed at indentation 5 should have the 2*space+tab marker." );
        self::assertSame( " \t",       $ws->trimIndentationLine( "       \t", 6 ),
                          "String with 7*space+tab trimmed at indentation 6 should have the 1*space+tab marker." );
        self::assertSame( "\t",        $ws->trimIndentationLine( "       \t", 7 ),
                          "String with 7*space+tab trimmed at indentation 7 should have the tab marker." );
        self::assertSame( "",          $ws->trimIndentationLine( "       \t", 8 ),
                          "String with 7*space+tab trimmed at indentation 8 should be empty." );
    }
}

?>
