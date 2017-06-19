<?php
namespace Olaoluwa98\Payant\Support;

use Olaoluwa98\Payant\Exceptions\ApiRequestError;
use Olaoluwa98\Payant\Exceptions\InvalidCredentials;
use Olaoluwa98\Payant\Exceptions\InvalidFeeBearer;
use Olaoluwa98\Payant\Exceptions\InvalidParameterType;
use Olaoluwa98\Payant\Exceptions\IsInvalid;
use Olaoluwa98\Payant\Exceptions\IsNull;
use Olaoluwa98\Payant\Exceptions\IsNullOrInvalid;
use Olaoluwa98\Payant\Exceptions\RequiredValueMissing;
use Olaoluwa98\Payant\Exceptions\RequiredValuesMissing;

use \Exception as phpException;

if (! function_exists('array_get'))
{
  /*
   *
   * @param array  $data
   * @param string $key
   * @param string $default
   *
   * @return mixed
   */
   function array_get($data, $key, $default = false) {
     if (!is_array($data)) {
         return $default;
     }
     return isset($data[$key]) ? $data[$key]: $default;
   }
}

if(!function_exists('array_keys_exist')){
    /**
     * Checks if multiple keys exist in an array
     *
     * @param array $array
     * @param array|string $keys
     *
     * @return bool
     */
    function array_keys_exist( array $array, $keys ) {
        $count = 0;
        if ( ! is_array( $keys ) ) {
            $keys = func_get_args();
            array_shift( $keys );
        }
        foreach ( $keys as $key ) {
            if ( array_key_exists( $key, $array ) ) {
                $count ++;
            }
        }

        return count( $keys ) === $count;
    }
}

function cleanResponse($response){
	$result = $response->getBody();
	return json_decode($result);
}