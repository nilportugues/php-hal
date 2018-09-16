<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/18/15
 * Time: 10:41 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Tests\Api\Hal\Dummy\ComplexObject;

use NilPortugues\Tests\Api\Hal\Dummy\ComplexObject\ValueObject\UserId;

class User
{
    /**
     * @var UserId
     */
    private $userId;
    /**
     * @var
     */
    private $name;

    /**
     * @var
     */
    private $password;

    /**
     * @param UserId $id
     * @param $name
     */
    public function __construct(UserId $id, $name, $password)
    {
        $this->userId = $id;
        $this->name = $name;
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }
}
