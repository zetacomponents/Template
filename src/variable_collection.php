<?php
/**
 * File containing the ezcTemplateVariableCollection class
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
 * Contains template variables which are sent and received from templates.
 *
 * The class provides a convenient way to set and get variables as normal
 * PHP attributes. It also works as an iterator for traverals of the
 * variables.
 *
 * NOTE: If you want to assign an array be VERY careful. Do NOT write:
 * <code>
 * $v->myProp = array();
 * $v->myProp[] = "Hello";
 * </code>
 * Because 'myProp' will still be an empty array. (Stupid PHP)
 *
 * The next example sets and gets variables:
 *
 * <code>
 * $collection = new ezcTemplateVariableCollection();
 * $collection->character = "Londo Mollari";
 * $collection->actor = "Peter Jurasik";
 * $collection->race = "Centauri";
 *
 * echo "Race: " . $collection->race;
 * </code>
 *
 * The next example shows all variables using the iterator.
 * <code>
 * $send = new ezcTemplateVariableCollection();
 *
 * $send->red = "FF0000";
 * $send->green = "00FF00";
 * $send->blue = "0000FF";
 *
 * foreach ( $send as $name => $value )
 * {
 *     echo "$name  -> $value\n";
 * }
 * </code>
 *
 * @package Template
 * @version //autogen//
 */
class ezcTemplateVariableCollection implements Iterator
{
    /**
     * The collection where all variables are stored. Each entry is a
     * normal PHP value and is looked up with the name of the variable.
     * @var array(string=>mixed)
     */
    private $variables;

    /**
     * Initialises an empty collection of variables.
     *
     * Note: To initialise it with existing variables pass them as the $variables
     * parameter.
     *
     * @param array(string=>mixed) $variables An array of variables to initialise the collection
     * with. The default is an empty collection.
     */
    public function __construct( $variables = array() )
    {
        $this->variables = $variables;
    }

    /**
     * Returns the value of the variable $name.
     *
     * @param string $name
     * @return mixed
     */
    public function __get( $name )
    {
        if ( isset( $this->variables[$name] ) )
        {
            $value = $this->variables[$name];
            if ( is_array( $value ) )
            {
                // Ridiculous but needed.
                return (array) $value;
            }
            else
            {
                return $value;
            }
        }
        else
        {
            return null;
        }
    }

    /**
     * Sets the value in $value to the variable named $name.
     *
     * NOTE: If you want to assign an array be VERY careful. Do NOT write:
     * <code>
     * $v->myProp = array();
     * $v->myProp[] = "Hello";
     * </code>
     * Because 'myProp' will still be an empty array. (Stupid PHP)
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set( $name, $value )
    {
        $this->variables[$name] = $value;
    }

    /**
     * Returns true if the variable $name is set.
     *
     * @param string $name
     * @return bool
     */
    public function __isset( $name )
    {
        return array_key_exists( $name, $this->variables ) && isset( $this->variables[$name] );
    }

    /**
     * Returns all variables in an array.
     *
     * @return array(string=>mixed)
     */
    public function getVariableArray()
    {
        return $this->variables;
    }

    /**
     * Iterator rewind method
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        reset( $this->variables );
    }

    /**
     * Returns the current variable
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return current( $this->variables );
    }

    /**
     * Returns the current key.
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return key( $this->variables );
    }

    /**
     * Proceed to the next element.
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        return next( $this->variables );
    }

    /**
     * Returns true if the iterator is at a valid location.
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return ( $this->current() !== false );
    }

    /**
     * Removes the variable named $name from the collection.
     *
     * If the variable doesn't exist, it returns false.
     *
     * @param string $name The name of the variable to remove.
     * @return bool
     */
    public function removeVariable( $name )
    {
        if ( !isset( $this->variables[$name] ) )
        {
            return false;
        }

        unset( $this->variables[$name] );
        return true;
    }
}
?>
