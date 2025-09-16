<?php

namespace Tests;

use App\Services\MidtransService;
use PHPUnit\Framework\TestCase;

class MidtransServiceTest extends TestCase
{
    /**
     * Test that the Midtrans service can be instantiated
     *
     * @return void
     */
    public function testMidtransServiceCanBeInstantiated()
    {
        $midtransService = new MidtransService();
        $this->assertInstanceOf(MidtransService::class, $midtransService);
    }

    /**
     * Test that the service has the required configuration
     *
     * @return void
     */
    public function testMidtransServiceHasConfiguration()
    {
        $midtransService = new MidtransService();
        
        // Since we're in a test environment, we can't check actual values
        // but we can check that the properties exist
        $this->assertClassHasAttribute('serverKey', MidtransService::class);
        $this->assertClassHasAttribute('clientKey', MidtransService::class);
        $this->assertClassHasAttribute('isProduction', MidtransService::class);
    }
}