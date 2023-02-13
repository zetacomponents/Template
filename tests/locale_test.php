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
class ezcTemplateLocaleTest extends ezcTestCase
{
    private string $basePath;
    private string $templatePath;
    private string $compilePath;

    public static function suite()
    {
         return new PHPUnit\Framework\TestSuite( __CLASS__ );
    }

    protected function setUp() : void
    {

        try
        {
            $this->setLocale( LC_ALL, 'de_DE', 'de_DE.UTF-8', 'deu', 'german' );
        }
        catch ( PHPUnit_Framework_Exception $e )
        {
            $this->markTestSkipped( 'System does not support setting locale to de_DE.UTF-8.' );
        }

        $this->basePath = $this->createTempDir( "ezcTemplate_" );
        $this->templatePath = $this->basePath . "/templates";
        $this->compilePath = $this->basePath . "/compiled";

        mkdir ( $this->templatePath );
        mkdir ( $this->compilePath );

        $config = ezcTemplateConfiguration::getInstance();
        $config->templatePath = $this->templatePath;
        $config->compilePath = $this->compilePath;
        $config->context = new ezcTemplateNoContext;
    }

    protected function tearDown() : void
    {
        $this->removeTempDir();
    }

    public function testFloats()
    {
        $this->setLocale( LC_ALL, 'de_DE', 'de_DE.UTF-8', 'deu', 'german' );

        $template = new ezcTemplate();
        $file =  "float.ezt";

        // The number 3.14 should not be translated to 3,14. The array size should be one, not two.
        file_put_contents( $this->templatePath . "/". $file, "{array_count(array(3.14))}" );
        $this->assertEquals(1, $template->process( $file ), "Number 3.14 is internally translated to 3,14 when the de_DE locale is used.");
    }
}
?>
