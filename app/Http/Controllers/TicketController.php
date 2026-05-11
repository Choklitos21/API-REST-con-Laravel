<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Throwable;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $tickets = Ticket::with(['user', 'assignee', 'device'])->get();

            return response()->json($tickets, 200);
        } catch (Throwable $exception) {
            return $this->handleInternalServerError($request, $exception, 'Tickets index error');
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $ticket = Ticket::with(['user', 'assignee', 'device'])->findOrFail($id);

            return response()->json($ticket, 200);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'message' => 'Ticket not found',
            ], 404);
        } catch (Throwable $exception) {
            return $this->handleInternalServerError($request, $exception, 'Ticket show error');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string'],
                'status' => ['required', 'string', 'max:50'],
                'priority' => ['required', 'string', 'max:50'],
                'user_id' => ['required', 'integer', 'exists:users,id'],
                'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
                'device_id' => ['nullable', 'integer', 'exists:devices,id'],
            ]);

            $ticket = Ticket::create($data);

            return response()->json([
                'message' => 'Ticket created successfully',
                'ticket' => $ticket->load(['user', 'assignee', 'device']),
            ], 201);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $exception->errors(),
            ], 422);
        } catch (Throwable $exception) {
            return $this->handleInternalServerError($request, $exception, 'Ticket store error');
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $ticket = Ticket::findOrFail($id);

            $data = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string'],
                'status' => ['required', 'string', 'max:50'],
                'priority' => ['required', 'string', 'max:50'],
                'user_id' => ['required', 'integer', 'exists:users,id'],
                'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
                'device_id' => ['nullable', 'integer', 'exists:devices,id'],
            ]);

            $ticket->update($data);

            return response()->json([
                'message' => 'Ticket updated successfully',
                'ticket' => $ticket->load(['user', 'assignee', 'device']),
            ], 200);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $exception->errors(),
            ], 422);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'message' => 'Ticket not found',
            ], 404);
        } catch (Throwable $exception) {
            return $this->handleInternalServerError($request, $exception, 'Ticket update error');
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $ticket = Ticket::findOrFail($id);
            $ticket->delete();

            return response()->json([
                'message' => 'Ticket deleted successfully',
            ], 200);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'message' => 'Ticket not found',
            ], 404);
        } catch (Throwable $exception) {
            return $this->handleInternalServerError($request, $exception, 'Ticket destroy error');
        }
    }
}
