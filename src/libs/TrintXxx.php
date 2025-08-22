<?php

namespace shiyunJK;

/**
 * 可以仿 AlibabaCloud 这个写法
 */

/**
 * Class AlibabaCloud
 *
 * @package   AlibabaCloud\Client
 * @mixin     \AlibabaCloud\IdeHelper
 */
class TrintXxx
{
    // use ClientTrait;

    /**
     * Version of the Client
     */
    const VERSION = '1.0.1';

    /**
     * This static method can directly call the specific service.
     *
     * @param string $product
     * @param array  $arguments
     *
     * @codeCoverageIgnore
     * @return object
     * @throws ClientException
     */
    public static function __callStatic($product, $arguments)
    {
        $product = \ucfirst($product);

        $product_class = 'AlibabaCloud' . '\\' . $product . '\\' . $product;

        if (\class_exists($product_class)) {
            return new $product_class;
        }

        // throw new ClientException(
        //     "May not yet support product $product quick access, "
        //         . 'you can use [Alibaba Cloud Client for PHP] to send any custom '
        //         . 'requests: https://github.com/aliyun/openapi-sdk-php-client/blob/master/docs/en-US/3-Request.md',
        //     SDK::SERVICE_NOT_FOUND
        // );
    }
}
