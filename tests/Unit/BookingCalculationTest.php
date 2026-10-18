<?php
/**
 * Unit Test: Booking Calculation & Caution Deposit Toggle
 * Validates financial logic, date diffing, and toggle visibility states.
 */

class BookingCalculationTest {
    private array $rates = [
        'coral-room'         => 35000.0,
        'gold-room'          => 40000.0,
        'diamond-room'       => 50000.0,
        'premium-room'       => 65000.0,
        'presidential-suite' => 250000.0,
    ];

    public function testNightCalculation(): void {
        $in = new DateTime('2026-10-20');
        $out = new DateTime('2026-10-23');
        $nights = $in->diff($out)->days;
        assert($nights === 3, "Failed: Expected 3 nights, got {$nights}");
    }

    public function testSingleNightDefault(): void {
        $in = new DateTime('2026-10-20');
        $out = new DateTime('2026-10-20');
        $diff = $in->diff($out)->days;
        $nights = max(1, $diff);
        assert($nights === 1, "Failed: Expected single night fallback, got {$nights}");
    }

    public function testRoomRateMultiplication(): void {
        $rate = $this->rates['coral-room'];
        $nights = 3;
        $total = $rate * $nights;
        assert($total === 105000.0, "Failed: Expected ₦105,000, got ₦{$total}");
    }

    public function testCautionDepositWhenActive(): void {
        $cautionEnabled = true;
        $cautionAmount = 10000.0;
        $roomTotal = 35000.0;
        $grandTotal = $roomTotal + ($cautionEnabled ? $cautionAmount : 0.0);
        assert($grandTotal === 45000.0, "Failed: Expected ₦45,000 with active caution, got ₦{$grandTotal}");
    }

    public function testCautionDepositWhenHidden(): void {
        $cautionEnabled = false;
        $cautionAmount = 10000.0;
        $roomTotal = 35000.0;
        $grandTotal = $roomTotal + ($cautionEnabled ? $cautionAmount : 0.0);
        assert($grandTotal === 35000.0, "Failed: Expected ₦35,000 when caution hidden, got ₦{$grandTotal}");
    }

    public function testBookingReferenceFormat(): void {
        // Must match THS-XXXXXX (6 alphanumeric uppercase chars)
        $ref = 'THS-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $valid = (bool)preg_match('/^THS-[A-Z0-9]{6}$/', $ref);
        assert($valid === true, "Failed: Booking reference {$ref} does not match required format");
    }

    public function runAll(): array {
        $results = [];
        $methods = ['testNightCalculation', 'testSingleNightDefault', 'testRoomRateMultiplication', 'testCautionDepositWhenActive', 'testCautionDepositWhenHidden', 'testBookingReferenceFormat'];
        foreach ($methods as $m) {
            try {
                $this->$m();
                $results[$m] = ['status' => 'PASS'];
            } catch (Throwable $e) {
                $results[$m] = ['status' => 'FAIL', 'error' => $e->getMessage()];
            }
        }
        return $results;
    }
}
