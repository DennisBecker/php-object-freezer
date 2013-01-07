<?php
/**
 * Object_Freezer
 *
 * Copyright (c) 2008-2013, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Object_Freezer
 * @subpackage Tests
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2008-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @since      File available since Release 1.0.0
 */

require_once 'Object/Freezer/Storage/CouchDB.php';

/**
 * Abstract base class for Object_Freezer_Storage_CouchDB test case classes.
 *
 * @package    Object_Freezer
 * @subpackage Tests
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2008-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version    Release: @package_version@
 * @link       http://github.com/sebastianbergmann/php-object-freezer/
 * @since      Class available since Release 1.0.0
 */
abstract class Object_Freezer_Storage_CouchDB_TestCase extends PHPUnit_Framework_TestCase
{
    protected $freezer;
    protected $storage;

    /**
     * @covers Object_Freezer_Storage_CouchDB::__construct
     * @covers Object_Freezer_Storage_CouchDB::setUseLazyLoad
     */
    protected function setUp($create = TRUE)
    {
        if (!@fsockopen(OBJECT_FREEZER_COUCHDB_HOST, OBJECT_FREEZER_COUCHDB_PORT, $errno, $errstr)) {
            $this->markTestSkipped(
              sprintf(
                'CouchDB not running on %s:%d.',
                OBJECT_FREEZER_COUCHDB_HOST,
                OBJECT_FREEZER_COUCHDB_PORT
              )
            );
        }

        $idGenerator = $this->getMock('Object_Freezer_IdGenerator');
        $idGenerator->expects($this->any())
                    ->method('getId')
                    ->will($this->onConsecutiveCalls('a', 'b', 'c'));

        $this->freezer = new Object_Freezer($idGenerator);

        $this->storage = new Object_Freezer_Storage_CouchDB(
          'test',
          $this->freezer,
          NULL,
          $this->useLazyLoad,
          OBJECT_FREEZER_COUCHDB_HOST,
          (int)OBJECT_FREEZER_COUCHDB_PORT
        );

        if ($create) {
            $this->storage->send('PUT', '/test');
        }
    }

    protected function tearDown()
    {
        if ($this->storage !== NULL) {
            $this->storage->send('DELETE', '/test/');
        }

        $this->freezer = NULL;
        $this->storage = NULL;
    }

    protected function getFrozenObjectFromStorage($id)
    {
        $buffer = $this->storage->send('GET', '/test/' . $id);
        $buffer = $buffer['body'];

        $frozenObject = json_decode($buffer, TRUE);
        unset($frozenObject['_rev']);

        return $frozenObject;
    }
}
