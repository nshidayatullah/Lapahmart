<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true), // Contoh: "Premium Wireless Headphones"
            'price' => $this->faker->randomFloat(2, 10, 1000), // Harga antara 10.00 - 1000.00
            'stock' => $this->faker->numberBetween(0, 500), // Stok antara 0-500
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * State untuk produk dengan stok habis
     */
    public function outOfStock(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'stock' => 0,
            ];
        });
    }

    /**
     * State untuk produk dengan stok banyak
     */
    public function inStock(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'stock' => $this->faker->numberBetween(50, 500),
            ];
        });
    }

    /**
     * State untuk produk dengan harga mahal
     */
    public function expensive(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'price' => $this->faker->randomFloat(2, 500, 5000),
            ];
        });
    }

    /**
     * State untuk produk dengan harga murah
     */
    public function cheap(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'price' => $this->faker->randomFloat(2, 1, 50),
            ];
        });
    }

    /**
     * State untuk produk dengan nama spesifik
     */
    public function withName(string $name): Factory
    {
        return $this->state(function (array $attributes) use ($name) {
            return [
                'name' => $name,
            ];
        });
    }
}
