<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Throwable;

class DeviceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $devices = Device::with('assignedUser')->get();

            return response()->json($devices, 200);
        } catch (Throwable $exception) {
            return $this->handleInternalServerError($request, $exception, 'Devices index error');
        }
    }

    public function assign(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'device_id' => ['required', 'integer', 'exists:devices,id'],
                'user_id' => ['nullable', 'integer', 'exists:users,id'],
            ]);

            $device = Device::findOrFail($data['device_id']);
            $device->assigned_user_id = $data['user_id'] ?? null;
            $device->save();

            return response()->json([
                'message' => 'Device assignment updated successfully',
                'device' => $device->load('assignedUser'),
            ], 200);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $exception->errors(),
            ], 422);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'message' => 'Device not found',
            ], 404);
        } catch (Throwable $exception) {
            return $this->handleInternalServerError($request, $exception, 'Device assign error');
        }
    }
}
