<?php

namespace app\tests\unit\models;

use app\models\LoanRequest;
use Codeception\Test\Unit;

class LoanRequestTest extends Unit
{
    public function testIsPending()
    {
        $request = new LoanRequest(['status' => LoanRequest::STATUS_PENDING]);
        $this->assertTrue($request->isPending());
        $this->assertFalse($request->isApproved());
    }

    public function testIsApproved()
    {
        $request = new LoanRequest(['status' => LoanRequest::STATUS_APPROVED]);
        $this->assertTrue($request->isApproved());
        $this->assertFalse($request->isPending());
    }
}
