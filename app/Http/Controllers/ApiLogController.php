<?php

namespace App\Http\Controllers;

use App\Models\ApiLog;
use App\Services\ApiLoggerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ApiLogController extends Controller
{
    /**
     * The API logger service instance.
     */
    protected ApiLoggerService $logger;

    /**
     * Create a new controller instance.
     */
    public function __construct(ApiLoggerService $logger)
    {
        $this->logger = $logger;
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of API logs.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = ApiLog::query();

        // Apply filters
        if ($request->filled('method')) {
            $query->method($request->method);
        }

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        if ($request->filled('url')) {
            $query->where('url', 'like', '%' . $request->url . '%');
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        if ($request->boolean('errors_only')) {
            $query->errors();
        }

        if ($request->boolean('slow_only')) {
            $threshold = config('api-logger.slow_request_threshold_ms', 1000);
            $query->slow($threshold);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $perPage = $request->get('per_page', 50);
        $logs = $query->paginate($perPage);

        // Get statistics
        $statistics = $this->logger->getStatistics($request->get('period', '24h'));

        if ($request->wantsJson()) {
            return response()->json([
                'logs' => $logs,
                'statistics' => $statistics,
            ]);
        }

        return view('api-logs.index', compact('logs', 'statistics'));
    }

    /**
     * Display the specified API log.
     */
    public function show(ApiLog $apiLog): View|JsonResponse
    {
        if (request()->wantsJson()) {
            return response()->json($apiLog);
        }

        return view('api-logs.show', compact('apiLog'));
    }

    /**
     * Get API statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $period = $request->get('period', '24h');
        $statistics = $this->logger->getStatistics($period);

        return response()->json($statistics);
    }

    /**
     * Export API logs.
     */
    public function export(Request $request): JsonResponse
    {
        $query = ApiLog::query();

        // Apply the same filters as index
        if ($request->filled('method')) {
            $query->method($request->method);
        }

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        $format = $request->get('format', 'json');
        $logs = $query->get();

        switch ($format) {
            case 'csv':
                return $this->exportToCsv($logs);
            case 'json':
            default:
                return response()->json($logs);
        }
    }

    /**
     * Delete the specified API log.
     */
    public function destroy(ApiLog $apiLog): JsonResponse
    {
        try {
            $apiLog->delete();
            return response()->json(['message' => 'API log deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete API log'], 500);
        }
    }

    /**
     * Bulk delete API logs.
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:api_logs,id',
        ]);

        try {
            $deletedCount = ApiLog::whereIn('id', $request->ids)->delete();
            return response()->json([
                'message' => "Successfully deleted {$deletedCount} API logs"
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete API logs'], 500);
        }
    }

    /**
     * Export logs to CSV format.
     */
    protected function exportToCsv($logs): JsonResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="api-logs-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'ID',
                'Method',
                'URL',
                'IP Address',
                'User Agent',
                'Response Status',
                'Response Time (ms)',
                'User ID',
                'Correlation ID',
                'Created At',
            ]);

            // CSV data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->method,
                    $log->url,
                    $log->ip_address,
                    $log->user_agent,
                    $log->response_status,
                    $log->response_time_ms,
                    $log->user_id,
                    $log->correlation_id,
                    $log->created_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 