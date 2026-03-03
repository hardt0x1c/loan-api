<?php

namespace app\commands;

use app\services\LoanProcessorService;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Console controller for processing loan requests in the background.
 */
class ProcessorController extends Controller
{
    /**
     * Processes pending loan requests.
     *
     * @param int $delay Artificial delay before decision making in seconds.
     * @return int
     */
    public function actionIndex(int $delay = 0): int
    {
        $service = new LoanProcessorService();

        $this->stdout("Resetting stale requests...
");
        $staleCount = $service->resetStaleRequests();
        if ($staleCount > 0) {
            $this->stdout("Reset {$staleCount} stuck requests back to pending.
");
        } else {
            $this->stdout("No stuck requests found.
");
        }

        $this->stdout("Processing pending requests with delay {$delay}s...
");
        
        // Use 0 for timeLimit to run until all pending requests are processed.
        $service->processPending($delay, 0);
        
        $this->stdout("Processing complete.
");

        return ExitCode::OK;
    }
}
