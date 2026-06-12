<?php

namespace App\Http\Controllers;

use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function __construct(private LicenseService $license) {}

    public function index()
    {
        return view('settings.license', [
            'status' => $this->license->status(),
        ]);
    }

    public function activate(Request $request): JsonResponse
    {
        $request->validate([
            'purchase_code' => ['required', 'string', 'min:6', 'max:100'],
        ]);

        $result = $this->license->activate($request->input('purchase_code'));

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'status'  => $result['success'] ? $this->license->status() : null,
        ], $result['success'] ? 200 : 422);
    }

    public function deactivate(Request $request): JsonResponse
    {
        $result = $this->license->deactivate();

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['success'] ? 200 : 422);
    }

    public function status(): JsonResponse
    {
        return response()->json($this->license->status());
    }
}
