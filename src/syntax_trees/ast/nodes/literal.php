<?php
/**
 * File containing the ezcTemplateLiteralAstNode class
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
 * Represents PHP builtin types.
 *
 * Creating the type is done by simply passing the type to the constructor
 * which will take care of storing it and exporting it to PHP code later on.
 * The following types can be added:
 * - integer
 * - float
 * - boolean
 * - null
 * - string
 * - array
 * - objects
 *
 * The following types are not supported:
 * - resource
 *
 * Note: Objects will have to implement the __set_state magic method to be
 *       properly exported.
 *
 * <code>
 * $tInt = new ezcTemplateLiteralAstNode( 5 );
 * $tFloat = new ezcTemplateLiteralAstNode( 5.2 );
 * $tString = new ezcTemplateLiteralAstNode( "a simple string" );
 * </code>
 *
 * @package Template
 * @version //autogen//
 * @access private
 */
class ezcTemplateLiteralAstNode extends ezcTemplateAstNode
{
    /**
     * The constant value for the type.
     *
     * @var mixed
     */
    public $value;

    /**
     * Checks and sets the type hint.
     *
     * @return void
     */
    public function checkAndSetTypeHint()
    {
        $this->typeHint = is_array( $this->value ) ? ezcTemplateAstNode::TYPE_ARRAY : ezcTemplateAstNode::TYPE_VALUE;
    }

    /**
     * Constructs a new Literal
     *
     * @param mixed $value The value of PHP type to be stored in code element.
     */
    public function __construct( $value )
    {
        parent::__construct();

        $this->value = $value;
        if ( is_resource( $value ) )
        {
            throw new ezcTemplateInternalException( "Cannot use resource for type codes, resources cannot be exported as text strings" );
        }

        // Check if the __set_state magic method is implemented
        if ( is_object( $value ) &&
             !method_exists( $value, "__set_state" ) )
        {
            throw new ezcTemplateInternalException( "The magic method __set_state is not implemented for passed object, the type code cannot create a representation of the object without it." );
        }

        $this->checkAndSetTypeHint();
    }
}
?>
