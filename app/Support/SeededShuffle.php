<?php

namespace App\Support;

/**
 * Deterministic shuffling with a LOCAL generator.
 *
 * Never touches mt_srand()/shuffle()/random_int(): the same seed always yields
 * the same order, and no other code can disturb it (or be disturbed by it).
 * The generator is mulberry32, kept to 32-bit arithmetic with explicit masks
 * so results are identical on every 64-bit PHP build.
 */
final class SeededShuffle
{
    private int $state;

    public function __construct(int $seed)
    {
        $this->state = $seed & 0xFFFFFFFF;
    }

    /** Stable non-randomized 32-bit seed from any parts (crc32 of the joined string). */
    public static function seed(string ...$parts): int
    {
        return crc32(implode(':', $parts)) & 0xFFFFFFFF;
    }

    /**
     * Fisher–Yates using this generator. Returns a new list; keys are discarded.
     *
     * @template T
     *
     * @param  array<int|string, T>  $items
     * @return list<T>
     */
    public static function shuffle(array $items, int $seed): array
    {
        $items = array_values($items);
        $generator = new self($seed);

        for ($i = count($items) - 1; $i > 0; $i--) {
            $j = $generator->nextInt($i + 1);
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }

        return $items;
    }

    /** Uniform-enough integer in [0, $max). */
    public function nextInt(int $max): int
    {
        return (int) floor($this->next() * $max);
    }

    /** Next float in [0, 1). */
    public function next(): float
    {
        $this->state = ($this->state + 0x6D2B79F5) & 0xFFFFFFFF;
        $t = $this->state;
        $t = self::imul($t ^ ($t >> 15), $t | 1);
        $t ^= ($t + self::imul($t ^ ($t >> 7), $t | 61)) & 0xFFFFFFFF;
        $t &= 0xFFFFFFFF;

        return (($t ^ ($t >> 14)) & 0xFFFFFFFF) / 4294967296;
    }

    /** 32-bit integer multiply (like JavaScript's Math.imul), unsigned result. */
    private static function imul(int $a, int $b): int
    {
        $a &= 0xFFFFFFFF;
        $b &= 0xFFFFFFFF;
        $low = ($a & 0xFFFF) * $b;
        $high = (($a >> 16) * $b) & 0xFFFF;

        return ($low + ($high << 16)) & 0xFFFFFFFF;
    }
}
