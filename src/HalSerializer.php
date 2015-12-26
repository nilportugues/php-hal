<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 12/17/15
 * Time: 1:52 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Api\Hal;

use NilPortugues\Serializer\DeepCopySerializer;

/**
 * Class HalJsonSerializer.
 */
class HalSerializer  extends DeepCopySerializer
{
    /**
     * @var HalTransformer
     */
    protected $serializationStrategy;

    /**
     * @param HalTransformer $strategy
     */
    public function __construct(HalTransformer $strategy)
    {
        parent::__construct($strategy);
    }

    /**
     * @return HalTransformer
     */
    public function getTransformer()
    {
        return $this->serializationStrategy;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        return parent::serialize($value);
    }
}
