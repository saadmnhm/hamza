<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

if (! function_exists('getList')) {
    /**
     * Fetch table or model data and return as JSON response.
     *
     * @param  string  $target  Model class (e.g., App\Models\User::class) or table name (e.g., 'users')
     * @param  array  $columns  Columns to select (default: ['*'])
     * @param  array  $where  Conditions array (e.g., ['status' => 'active'])
     * @param  int  $perPage  Pass > 0 to enable pagination
     */
    function getList(
        string $target,
        array $columns = ['*'],
        array $where = [],
        int $perPage = 0
    ): JsonResponse {
        try {
            // Determine if $target is an Eloquent Model or DB table name
            if (class_exists($target) && is_subclass_of($target, Model::class)) {
                $query = $target::query();
            } else {
                $query = DB::table($target);
            }

            // Apply select columns
            $query->select($columns);

            // Apply filters if provided
            if (! empty($where)) {
                $query->where($where);
            }

            // Paginate or get all
            $data = $perPage > 0 ? $query->paginate($perPage) : $query->get();

            return response()->json([
                'success' => true,
                'data' => $data,
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
