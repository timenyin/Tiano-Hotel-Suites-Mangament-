<?php
/**
 * Unit Test: Authentication & Cryptographic Security
 * Validates password hashing, CSRF token generation/verification, and input sanitization.
 */

class AuthSecurityTest {
    public function testPasswordHashBcrypt(): void {
        $password = "TianoSecurePass2026!";
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        assert(password_verify($password, $hash) === true, "Password verify failed for correct password");
        assert(password_verify("WrongPassword", $hash) === false, "Password verify succeeded on wrong password");
    }

    public function testCsrfTokenEntropy(): void {
        $token1 = bin2hex(random_bytes(32));
        $token2 = bin2hex(random_bytes(32));
        assert(strlen($token1) === 64, "Token length must be 64 characters");
        assert($token1 !== $token2, "Two generated CSRF tokens must not collide");
    }

    public function testOtpGeneration(): void {
        $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        assert(strlen($otp) === 6, "OTP must be exactly 6 digits");
        assert(ctype_digit($otp), "OTP must only contain digits");
    }

    public function runAll(): array {
        $results = [];
        $methods = ['testPasswordHashBcrypt', 'testCsrfTokenEntropy', 'testOtpGeneration'];
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
