<?php

namespace App\Http\Controllers;

use App\Services\ServiceClient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiGatewayController extends Controller
{
    /**
     * Route to Product Service
     */
    public function product(Request $request): JsonResponse
    {
        $client = new ServiceClient('product');
        $method = strtolower($request->method());
        $path = $request->route('path') ?? '';
        
        $response = match($method) {
            'get' => $client->get($path, $request->all()),
            'post' => $client->post($path, $request->all()),
            'put' => $client->put($path, $request->all()),
            'delete' => $client->delete($path),
            default => ['success' => false, 'error' => 'Method not allowed'],
        };

        return response()->json($response['data'] ?? $response, $response['status'] ?? 200);
    }

    /**
     * Route to Order Service
     */
    public function order(Request $request): JsonResponse
    {
        $client = new ServiceClient('order');
        $method = strtolower($request->method());
        $path = $request->route('path') ?? '';
        
        $response = match($method) {
            'get' => $client->get($path, $request->all()),
            'post' => $client->post($path, $request->all()),
            'put' => $client->put($path, $request->all()),
            'delete' => $client->delete($path),
            default => ['success' => false, 'error' => 'Method not allowed'],
        };

        return response()->json($response['data'] ?? $response, $response['status'] ?? 200);
    }

    /**
     * Route to User Service
     */
    public function user(Request $request): JsonResponse
    {
        $client = new ServiceClient('user');
        $method = strtolower($request->method());
        $path = $request->route('path') ?? '';
        
        $response = match($method) {
            'get' => $client->get($path, $request->all()),
            'post' => $client->post($path, $request->all()),
            'put' => $client->put($path, $request->all()),
            'delete' => $client->delete($path),
            default => ['success' => false, 'error' => 'Method not allowed'],
        };

        return response()->json($response['data'] ?? $response, $response['status'] ?? 200);
    }

    /**
     * Route to Payment Service
     */
    public function payment(Request $request): JsonResponse
    {
        $client = new ServiceClient('payment');
        $method = strtolower($request->method());
        $path = $request->route('path') ?? '';
        
        $response = match($method) {
            'get' => $client->get($path, $request->all()),
            'post' => $client->post($path, $request->all()),
            'put' => $client->put($path, $request->all()),
            'delete' => $client->delete($path),
            default => ['success' => false, 'error' => 'Method not allowed'],
        };

        return response()->json($response['data'] ?? $response, $response['status'] ?? 200);
    }

    /**
     * Route to Inventory Service
     */
    public function inventory(Request $request): JsonResponse
    {
        $client = new ServiceClient('inventory');
        $method = strtolower($request->method());
        $path = $request->route('path') ?? '';
        
        $response = match($method) {
            'get' => $client->get($path, $request->all()),
            'post' => $client->post($path, $request->all()),
            'put' => $client->put($path, $request->all()),
            'delete' => $client->delete($path),
            default => ['success' => false, 'error' => 'Method not allowed'],
        };

        return response()->json($response['data'] ?? $response, $response['status'] ?? 200);
    }

    /**
     * Route to Notification Service
     */
    public function notification(Request $request): JsonResponse
    {
        $client = new ServiceClient('notification');
        $method = strtolower($request->method());
        $path = $request->route('path') ?? '';
        
        $response = match($method) {
            'get' => $client->get($path, $request->all()),
            'post' => $client->post($path, $request->all()),
            'put' => $client->put($path, $request->all()),
            'delete' => $client->delete($path),
            default => ['success' => false, 'error' => 'Method not allowed'],
        };

        return response()->json($response['data'] ?? $response, $response['status'] ?? 200);
    }
}

