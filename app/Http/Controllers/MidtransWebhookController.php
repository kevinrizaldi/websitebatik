<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Midtrans\MidtransService;
use App\Services\Midtrans\PaymentAttemptProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtransService,
        private readonly PaymentAttemptProcessor $processor,
    ) {}

    /**
     * Handle a Midtrans payment notification webhook.
     *
     * POST /midtrans/notification
     */
    public function handle(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $payload */
        $payload = $request->json()->all();

        // -- 1. Validate required fields (strings, non-empty) -------------------
        $requiredFields = ['order_id', 'status_code', 'gross_amount', 'signature_key', 'transaction_status'];
        foreach ($requiredFields as $field) {
            if (! isset($payload[$field]) || ! is_string($payload[$field]) || $payload[$field] === '') {
                return response()->json(['message' => "Missing or invalid field: {$field}"], 400);
            }
        }

        // Validate optional fraud_status type before any DB work
        $rawFraudStatus = $payload['fraud_status'] ?? null;
        if ($rawFraudStatus !== null && ! is_string($rawFraudStatus)) {
            return response()->json(['message' => 'Invalid field: fraud_status'], 400);
        }

        // -- 2. Verify signature before any DB work ----------------------------
        if (! $this->midtransService->verifySignature($payload)) {
            Log::warning('Midtrans webhook: invalid signature', [
                'order_id' => $payload['order_id'] ?? 'unknown',
            ]);

            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        // -- 3. Lookup payment attempt by midtrans_order_id (unlocked read) ----
        $payment = Payment::where('midtrans_order_id', $payload['order_id'])->first();
        if (! $payment) {
            return response()->json(['message' => 'Payment attempt not found.'], 404);
        }

        // -- 4. Delegate transition logic to PaymentAttemptProcessor -----------
        $result = $this->processor->apply($payment, $payload);

        return response()->json(['message' => $result['message']], $result['code']);
    }
}
