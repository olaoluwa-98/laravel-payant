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
        $response = $this->payant->shouldReceive('getBanks')->andReturn(['banks']);

        $this->assertEquals('array', gettype(array($response)));
    }

    public function testResolveAccount()
    {
        $arg = ['settlement_bank' => 037, 'account_number' => 220];
        $response = $this->payant->shouldReceive('resolveAccount')->with($arg)->andReturn('resolved-account');

        $this->assertEquals('array', gettype(array($response)));
    }

    public function testAddClient()
    {
        $arg = ['first_name' => 'Emmanuel', 'last_name' => 'Awotunde', 'email' => 'awotunde.emmanuel1@gmail.com', 'phone' => '08090579032'];
        $response = $this->payant->shouldReceive('addClient')->with($arg)->andReturn('added-client');
        $this->assertEquals('array', gettype(array($response)));
    }
}
