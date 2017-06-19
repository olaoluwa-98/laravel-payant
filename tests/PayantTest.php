<?php

/*
 * This file is part of the Laravel Payant package.
 *
 * (c) Emmanuel Awotunde <awotunde.emmanuel1@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Olaoluwa98\Payant\Test;

use Mockery as m;
use GuzzleHttp\Client;
use PHPUnit_Framework_TestCase;
use Olaoluwa98\Payant\Payant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Facade as Facade;

class PayantTest extends PHPUnit_Framework_TestCase
{
    protected $payant;

    public function setUp()
    {
        $this->payant = m::mock('Olaoluwa98\Payant\Payant');
        $this->mock = m::mock('GuzzleHttp\Client');
    }

    public function tearDown()
    {
        m::close();
    }

    public function testAllBanksAreReturned()
    {
        $array = $this->payant->shouldReceive('getBanks')->andReturn(['prosper']);

        $this->assertEquals('array', gettype(array($array)));
    }

    public function testAllTransactionsAreReturned()
    {
        $array = $this->payant->shouldReceive('getAllTransactions')->andReturn(['transactions']);

        $this->assertEquals('array', gettype(array($array)));
    }

    public function testAllPlansAreReturned()
    {
        $array = $this->payant->shouldReceive('getAllPlans')->andReturn(['intermediate-plan']);

        $this->assertEquals('array', gettype(array($array)));
    }
}
