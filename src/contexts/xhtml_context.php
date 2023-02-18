<?php
/**
 * File containing the ezcTemplateXhtmlContext class
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
 */

/**
 * The ezcTemplateXhtmlContext class escapes special HTML characters in the
 * output.
 *
 * @package Template
 * @version //autogen//
 */

class ezcTemplateXhtmlContext implements ezcTemplateOutputContext
{
    /**
     * Escapes special HTML characters in the output.
     *
     * @param ezcTemplateAstNode $node
     * @return ezcTemplateAstNode The new AST node which should replace $node.
     */
    public function transformOutput( ezcTemplateAstNode $node )
    {
        return new ezcTemplateFunctionCallAstNode( "htmlspecialchars", array( $node, new ezcTemplateConstantAstNode( ENT_QUOTES | ENT_SUBSTITUTE ) ) );
    }

    /**
     * Returns the unique identifier for the context handler.
     *
     * @return string
     */
    public function identifier()
    {
        return 'xhtml';
    }
}
?>
