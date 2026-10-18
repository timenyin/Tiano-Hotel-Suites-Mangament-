<?php
/**
 * Tiano Hotel & Suites - Automated Test Runner
 * Executes all Unit and SEO Audit Test suites.
 */

require_once __DIR__ . '/Unit/BookingCalculationTest.php';
require_once __DIR__ . '/Unit/AuthSecurityTest.php';
require_once __DIR__ . '/Unit/SeoAuditTest.php';

echo "\n======================================================\n";
echo "  TIANO HOTEL & SUITES — AUTOMATED UNIT TEST RUNNER   \n";
echo "======================================================\n\n";

$suites = [
    'Booking & Financial Calculation Suite' => new BookingCalculationTest(),
    'Authentication & Security Suite'       => new AuthSecurityTest(),
    'SEO, Robots & Canonical Audit Suite'   => new SeoAuditTest(),
];

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

foreach ($suites as $suiteName => $suite) {
    echo "▶ Running Suite: {$suiteName}\n";
    $results = $suite->runAll();

    foreach ($results as $method => $res) {
        $totalTests++;
        if ($res['status'] === 'PASS') {
            $passedTests++;
            echo "   [PASS] {$method}\n";
        } else {
            $failedTests++;
            echo "   [FAIL] {$method}: {$res['error']}\n";
        }
    }
    echo "\n";
}

echo "------------------------------------------------------\n";
echo "Test Execution Summary:\n";
echo "Total Tests Run : {$totalTests}\n";
echo "Passed          : {$passedTests}\n";
echo "Failed          : {$failedTests}\n";
echo "Result Status   : " . ($failedTests === 0 ? "SUCCESS (100% Passed)\n" : "FAILED\n");
echo "======================================================\n\n";

exit($failedTests === 0 ? 0 : 1);
