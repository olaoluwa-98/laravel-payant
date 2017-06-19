<?php

/*
 * This file is part of the Laravel Payant package.
 *
 * (c) Emmanuel Awotunde <awotunde.emmanuel1@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Olaoluwa98\Payant\Facades;

use Illuminate\Support\Facades\Facade;

class Payant extends Facade
{
    /**
     * Get the registered name of the component
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-payant';
    }
}
