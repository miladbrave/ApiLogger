<?php

namespace Database\Factories;

use App\Models\ApiLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApiLog>
 */
class ApiLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ApiLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        $statusCodes = [200, 201, 400, 401, 403, 404, 422, 500];
        $urls = [
            '/api/users',
            '/api/posts',
            '/api/comments',
            '/api/auth/login',
            '/api/auth/register',
            '/api/products',
            '/api/orders',
            '/api/categories',
        ];

        return [
            'method' => $this->faker->randomElement($methods),
            'url' => $this->faker->randomElement($urls),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'request_headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => $this->faker->userAgent(),
                'X-Requested-With' => 'XMLHttpRequest',
            ],
            'request_body' => json_encode([
                'name' => $this->faker->name(),
                'email' => $this->faker->email(),
                'message' => $this->faker->sentence(),
            ]),
            'response_headers' => [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-cache',
            ],
            'response_body' => json_encode([
                'success' => true,
                'message' => 'Operation completed successfully',
                'data' => [
                    'id' => $this->faker->uuid(),
                    'created_at' => now()->toISOString(),
                ],
            ]),
            'response_status' => $this->faker->randomElement($statusCodes),
            'response_time_ms' => $this->faker->numberBetween(50, 2000),
            'user_id' => $this->faker->optional()->uuid(),
            'session_id' => $this->faker->optional()->uuid(),
            'correlation_id' => $this->faker->uuid(),
            'metadata' => [
                'route_name' => $this->faker->optional()->word(),
                'route_action' => $this->faker->optional()->word(),
                'route_parameters' => [],
                'query_parameters' => [],
                'content_type' => 'application/json',
                'accept' => 'application/json',
                'referer' => $this->faker->optional()->url(),
                'origin' => $this->faker->optional()->url(),
            ],
        ];
    }

    /**
     * Indicate that the log is for a successful request.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'response_status' => $this->faker->randomElement([200, 201]),
            'response_time_ms' => $this->faker->numberBetween(50, 500),
        ]);
    }

    /**
     * Indicate that the log is for an error request.
     */
    public function error(): static
    {
        return $this->state(fn (array $attributes) => [
            'response_status' => $this->faker->randomElement([400, 401, 403, 404, 422, 500]),
            'response_time_ms' => $this->faker->numberBetween(100, 3000),
        ]);
    }

    /**
     * Indicate that the log is for a slow request.
     */
    public function slow(): static
    {
        return $this->state(fn (array $attributes) => [
            'response_time_ms' => $this->faker->numberBetween(1000, 5000),
        ]);
    }

    /**
     * Indicate that the log is for a GET request.
     */
    public function get(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'GET',
            'request_body' => null,
        ]);
    }

    /**
     * Indicate that the log is for a POST request.
     */
    public function post(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'POST',
        ]);
    }
} 