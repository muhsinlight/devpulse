<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;

class PublicHttpUrl implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * Blocks localhost and private/reserved IP literals so monitors cannot
     * target the DevPulse host's internal network. Does not resolve DNS
     * (full rebinding protection is out of scope for this learning app).
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            $fail('The :attribute must be a valid public HTTP(S) URL.');

            return;
        }

        $parts = parse_url($value);

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            $fail('The :attribute must be a valid public HTTP(S) URL.');

            return;
        }

        $scheme = Str::lower($parts['scheme']);

        if (! in_array($scheme, ['http', 'https'], true)) {
            $fail('The :attribute must use http or https.');

            return;
        }

        $host = Str::lower(trim($parts['host'], '[]'));

        if ($this->isBlockedHost($host)) {
            $fail('The :attribute must not target private or local network addresses.');
        }
    }

    private function isBlockedHost(string $host): bool
    {
        if ($host === 'localhost' || Str::endsWith($host, '.localhost') || Str::endsWith($host, '.local')) {
            return true;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $this->isPrivateOrReservedIp($host);
        }

        return false;
    }

    private function isPrivateOrReservedIp(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return ! filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
            );
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return ! filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
            );
        }

        return true;
    }
}
