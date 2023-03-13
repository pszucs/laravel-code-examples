<?php

namespace App\Rules;

use App\Values\Countries;
use Illuminate\Contracts\Validation\Rule;

class CountryCode implements Rule
{
    protected ?array $allowedCountryCodes;

    public function __construct(?array $allowedCountryCodes)
    {
        return $this->allowedCountryCodes = $allowedCountryCodes;
    }

    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return true;
        }

        $countries = $this->allowedCountryCodes ?? Countries::codesAsArray();

        return in_array($value, $countries, true);
    }

    public function message()
    {
        if (empty($this->allowedCountryCodes)) {
            return 'The selected country is invalid.';
        }

        $countryNames = array_map(
            fn(string $countryCode) => Countries::getName($countryCode),
            $this->allowedCountryCodes,
        );

        return 'The country must be ' . join(' or ', $countryNames) . '.';
    }

    public static function any(): self
    {
        return new static(null);
    }

    public static function oneOf(string ...$countryCodes): self
    {
        return new static($countryCodes);
    }

    public static function uk(): self
    {
        return static::oneOf(...array_keys(Countries::UK));
    }
}
