<?php

namespace app\services;

class LoanDecisionEngine
{
    /**
     * @return bool True if loan is approved, false otherwise.
     */
    public function shouldApprove(): bool
    {
        return random_int(1, 10) === 1;
    }
}
