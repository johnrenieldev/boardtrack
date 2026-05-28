<?php
/**
 * BoardTrack — TOTP Helper
 * app/helpers/TOTP.php
 *
 * Pure-PHP TOTP (RFC 6238) implementation for Google Authenticator.
 * No external library required — uses only PHP built-ins.
 *
 * Usage:
 *   $secret  = TOTP::generateSecret();
 *   $qrUrl   = TOTP::getQRCodeUrl('user@example.com', $secret, 'BoardTrack');
 *   $valid   = TOTP::verify($secret, $_POST['code']);
 */

class TOTP
{
    // Number of 30-second windows to check either side of now (allows clock drift)
    private const WINDOW = 2;
    private const DIGITS = 6;
    private const PERIOD = 30;

    // Base32 alphabet
    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a cryptographically random Base32 secret (160 bits = 32 chars)
     */
    public static function generateSecret(): string
    {
        $bytes = random_bytes(20); // 160 bits
        return self::base32Encode($bytes);
    }

    /**
     * Verify a 6-digit TOTP code against a secret.
     * Checks current window ± WINDOW to allow slight clock drift.
     *
     * @param string $secret  Base32 secret stored for the user
     * @param string $code    6-digit code from Google Authenticator
     * @return bool
     */
    public static function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if (!preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $key  = self::base32Decode($secret);
        $time = (int) floor(time() / self::PERIOD);

        for ($i = -self::WINDOW; $i <= self::WINDOW; $i++) {
            $generated = self::hotp($key, $time + $i);
            // Constant-time comparison
            if (hash_equals(str_pad((string) $generated, self::DIGITS, '0', STR_PAD_LEFT), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate TOTP code for a given timestamp (useful for testing)
     */
    public static function generate(string $secret, ?int $timestamp = null): string
    {
        $key  = self::base32Decode($secret);
        $time = (int) floor(($timestamp ?? time()) / self::PERIOD);
        return str_pad((string) self::hotp($key, $time), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Build a Google Authenticator QR-code URL via Google Charts API.
     * The secret is never embedded directly in page HTML — only the URL goes to the img src.
     *
     * @param string $email    User's email (account name shown in GA)
     * @param string $secret   Base32 secret
     * @param string $issuer   App name shown in GA (e.g. 'BoardTrack')
     * @return string          otpauth:// URI (pass to a QR encoder)
     */
    public static function getOtpAuthUrl(string $email, string $secret, string $issuer = 'BoardTrack'): string
    {
        $label = rawurlencode($issuer . ':' . $email);
        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&algorithm=SHA1&digits=%d&period=%d',
            $label,
            strtoupper($secret),
            rawurlencode($issuer),
            self::DIGITS,
            self::PERIOD
        );
    }

    /**
     * Return a Google Chart QR image URL for the otpauth URI.
     * Uses HTTPS — no secret travels in the URL except the Base32 value
     * which is what GA needs by design.
     */
    public static function getQRCodeUrl(string $email, string $secret, string $issuer = 'BoardTrack'): string
    {
        $otpauth = self::getOtpAuthUrl($email, $secret, $issuer);
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&ecc=M&margin=0&data=' . rawurlencode($otpauth);
    }

    /**
     * Generate 8 one-time recovery codes (plain text, returned once — then store hashed).
     *
     * @return array ['plain' => [...], 'hashed' => [...]]
     */
    public static function generateRecoveryCodes(): array
    {
        $plain  = [];
        $hashed = [];
        for ($i = 0; $i < 8; $i++) {
            $code     = strtoupper(bin2hex(random_bytes(4))) . '-' . strtoupper(bin2hex(random_bytes(4)));
            $plain[]  = $code;
            $hashed[] = password_hash($code, PASSWORD_BCRYPT);
        }
        return ['plain' => $plain, 'hashed' => $hashed];
    }

    /**
     * Check a recovery code against the stored hashed set.
     * Removes the used code from the list (returns updated hashed array or false).
     *
     * @param string $input        Plain-text code the user typed
     * @param array  $hashedCodes  Array of bcrypt-hashed codes from DB
     * @return array|false         Updated hashed array (with used code removed) or false if invalid
     */
    public static function useRecoveryCode(string $input, array $hashedCodes): array|false
    {
        $input = strtoupper(preg_replace('/\s+/', '', $input));
        foreach ($hashedCodes as $idx => $hash) {
            if (password_verify($input, $hash)) {
                array_splice($hashedCodes, $idx, 1);
                return $hashedCodes; // caller saves this back to DB
            }
        }
        return false;
    }

    // Internal helper methods.

    /**
     * HMAC-based One-Time Password (RFC 4226)
     */
    private static function hotp(string $key, int $counter): int
    {
        $data = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1', $data, $key, true);
        $offset = ord($hash[19]) & 0x0f;
        return (
            ((ord($hash[$offset])     & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8)  |
            ((ord($hash[$offset + 3]) & 0xff))
        ) % (10 ** self::DIGITS);
    }

    /**
     * Encode bytes to Base32
     */
    private static function base32Encode(string $bytes): string
    {
        $chars  = self::BASE32_CHARS;
        $result = '';
        $n      = strlen($bytes);
        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0; $i < $n; $i++) {
            $buffer    = ($buffer << 8) | ord($bytes[$i]);
            $bitsLeft += 8;
            while ($bitsLeft >= 5) {
                $bitsLeft -= 5;
                $result   .= $chars[($buffer >> $bitsLeft) & 31];
            }
        }
        if ($bitsLeft > 0) {
            $result .= $chars[($buffer << (5 - $bitsLeft)) & 31];
        }
        return $result;
    }

    /**
     * Decode Base32 to bytes
     */
    private static function base32Decode(string $b32): string
    {
        $b32    = strtoupper(preg_replace('/[^A-Z2-7]/', '', $b32));
        $chars  = self::BASE32_CHARS;
        $lookup = array_flip(str_split($chars));
        $result = '';
        $buffer = 0;
        $bitsLeft = 0;

        foreach (str_split($b32) as $char) {
            if (!isset($lookup[$char])) continue;
            $buffer    = ($buffer << 5) | $lookup[$char];
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $result   .= chr(($buffer >> $bitsLeft) & 0xff);
            }
        }
        return $result;
    }
}