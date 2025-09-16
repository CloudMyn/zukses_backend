<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MidtransAccountController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Check bank account information using Midtrans API
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAccount(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'bank' => 'required|string',
            'account_number' => 'required|string',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prepare account details
        $accountDetails = [
            'bank' => $request->bank,
            'account_number' => $request->account_number
        ];

        if ($request->has('first_name')) {
            $accountDetails['first_name'] = $request->first_name;
        }

        if ($request->has('last_name')) {
            $accountDetails['last_name'] = $request->last_name;
        }

        // Call Midtrans service to check account
        $result = $this->midtransService->checkBankAccount($accountDetails);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Account verification successful',
                'data' => $result['data']
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Account verification failed',
                'error' => $result['message']
            ], 400);
        }
    }

    /**
     * Get supported banks for account verification
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSupportedBanks()
    {
        $result = $this->midtransService->getSupportedBanks();

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Supported banks retrieved successfully',
                'data' => $result['data']
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve supported banks',
                'error' => $result['message']
            ], 400);
        }
    }
}