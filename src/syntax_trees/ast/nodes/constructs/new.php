<?php
/**
 * File containing the ezcTemplateEchoAstNode class
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
 * Represents a new construct.
 *
 * @package Template
 * @version //autogen//
 * @access private
 */
class ezcTemplateNewAstNode extends ezcTemplateParameterizedAstNode
{
    /**
     * The class name to create.
     *
     * @var string
     */
    public $class;

    /**
     * Constructs a 'new' class element.
     *
     * @param string $class
     * @param array(ezcTemplateAstNode) $functionArguments
     */
    public function __construct( $class = null, ?array $functionArguments = null )
    {
        parent::__construct();
        $this->class = $class;

        if ( $functionArguments !== null )
        {
            foreach ( $functionArguments as $argument )
            {
                $this->appendParameter( $argument );
            }
        }
    }

    /**
     * Validates the output parameters against their constraints.
     *
     * @return void
     */
    public function validate()
    {
    }
}
?>
