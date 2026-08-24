<?php
/**
 * Shared CSS builder utility for generating inline styles.
 *
 * Modern PHP 8.1+ implementation with strict types.
 *
 * @package Sky_Login_Redirect
 */

declare(strict_types=1);

namespace SkyLoginRedirect;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CSS builder utility class.
 */
final class CSSBuilder {
    /**
     * Accumulated CSS declarations.
     *
     * @var array<int, string>
     */
    private array $rules = [];

    /**
     * Add a CSS property with optional value, unit, and integer casting.
     *
     * @param string $property CSS property name.
     * @param mixed  $value    Value from option (can be string, false, or null).
     * @param string $unit     Optional unit suffix (px, %, em, etc.).
     * @param bool   $is_int   Cast value as integer.
     * @return self For method chaining.
     */
    public function add( string $property, mixed $value, string $unit = '', bool $is_int = false ): self {
        if ( '' === $value || false === $value || null === $value ) {
            return $this;
        }

        $val           = $is_int ? (int) $value : sanitize_text_field( (string) $value );
        $this->rules[] = "{$property}:{$val}{$unit};";

        return $this;
    }

    /**
     * Add a CSS property with a default fallback value.
     *
     * @param string $property CSS property name.
     * @param mixed  $value    Value from option (can be string, false, or null).
     * @param string $default_value Default value if option is empty.
     * @param string $unit          Optional unit suffix.
     * @param bool   $is_int        Cast value as integer.
     * @return self For method chaining.
     */
    public function addWithDefault( string $property, mixed $value, string $default_value, string $unit = '', bool $is_int = false ): self {
        if ( $value ) {
            $val           = $is_int ? (int) $value : sanitize_text_field( (string) $value );
            $this->rules[] = "{$property}:{$val}{$unit};";
        } else {
            $this->rules[] = "{$property}:{$default_value};";
        }

        return $this;
    }

    /**
     * Build the final CSS string.
     *
     * @return string CSS declarations.
     */
    public function build(): string {
        return implode( '', $this->rules );
    }

    /**
     * Reset the builder for reuse.
     *
     * @return self For method chaining.
     */
    public function reset(): self {
        $this->rules = [];
        return $this;
    }

    /**
     * Get rule count.
     *
     * @return int Number of rules added.
     */
    public function count(): int {
        return count( $this->rules );
    }
}
