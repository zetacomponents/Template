<?php
/**
 * File containing the ezcTemplateEmptyAstNode class
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
 * Represents an empty construct.
 *
 * @package Template
 * @version //autogen//
 * @access private
 */
class ezcTemplateEmptyAstNode extends ezcTemplateStatementAstNode
{
    /**
     * The expression to output when evaluated.
     * @var ezcTemplateAstNode
     */
    public $expression;

    /**
     * Initialize with function name code and optional arguments
     *
     * @param ezcTemplateAstNode $expression
     */
    public function __construct( ?ezcTemplateAstNode $expression = null )
    {
        parent::__construct();
        $this->expression = $expression;
    }

    /**
     * Validates the expression against its constraints.
     *
     * @throws ezcTemplateInternalException if the constraints are not met.
     * @return void
     */
    public function validate()
    {
        if ( $this->expression === null )
        {
            throw new ezcTemplateInternalException( "Missing expression for class <" . get_class( $this ) . ">." );
        }
    }
}
?>
