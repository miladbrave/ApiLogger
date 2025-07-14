<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ApiLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'method',
        'url',
        'ip_address',
        'user_agent',
        'request_headers',
        'request_body',
        'response_headers',
        'response_body',
        'response_status',
        'response_time_ms',
        'user_id',
        'session_id',
        'correlation_id',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'request_headers' => 'array',
        'response_headers' => 'array',
        'metadata' => 'array',
        'response_time_ms' => 'integer',
        'response_status' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'request_body',
        'response_body',
    ];

    /**
     * Scope a query to only include logs for a specific method.
     */
    public function scopeMethod(Builder $query, string $method): void
    {
        $query->where('method', strtoupper($method));
    }

    /**
     * Scope a query to only include logs for a specific status code.
     */
    public function scopeStatus(Builder $query, int $status): void
    {
        $query->where('response_status', $status);
    }

    /**
     * Scope a query to only include logs for a specific user.
     */
    public function scopeForUser(Builder $query, string $userId): void
    {
        $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include logs within a date range.
     */
    public function scopeDateRange(Builder $query, string $startDate, string $endDate): void
    {
        $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include logs with slow response times.
     */
    public function scopeSlow(Builder $query, int $thresholdMs = 1000): void
    {
        $query->where('response_time_ms', '>', $thresholdMs);
    }

    /**
     * Scope a query to only include error responses.
     */
    public function scopeErrors(Builder $query): void
    {
        $query->where('response_status', '>=', 400);
    }

    /**
     * Get the user associated with this log entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the formatted response time.
     */
    public function getFormattedResponseTimeAttribute(): string
    {
        if ($this->response_time_ms < 1000) {
            return $this->response_time_ms . 'ms';
        }

        return round($this->response_time_ms / 1000, 2) . 's';
    }

    /**
     * Get the status class for styling.
     */
    public function getStatusClassAttribute(): string
    {
        if ($this->response_status >= 500) {
            return 'error';
        }

        if ($this->response_status >= 400) {
            return 'warning';
        }

        if ($this->response_status >= 300) {
            return 'info';
        }

        return 'success';
    }

    /**
     * Check if the response is an error.
     */
    public function isError(): bool
    {
        return $this->response_status >= 400;
    }

    /**
     * Check if the response is successful.
     */
    public function isSuccess(): bool
    {
        return $this->response_status >= 200 && $this->response_status < 300;
    }

    /**
     * Get the request body as an array if it's JSON.
     */
    public function getRequestBodyArrayAttribute(): ?array
    {
        if (empty($this->request_body)) {
            return null;
        }

        $decoded = json_decode($this->request_body, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    /**
     * Get the response body as an array if it's JSON.
     */
    public function getResponseBodyArrayAttribute(): ?array
    {
        if (empty($this->response_body)) {
            return null;
        }

        $decoded = json_decode($this->response_body, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
} 