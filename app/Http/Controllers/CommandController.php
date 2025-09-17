<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CommandController extends Controller
{
    // Daftar perintah yang diizinkan
    private $allowedCommands = [
        'ls',
        'pwd',
        'whoami',
        'php artisan list',
        'php artisan --version',
        'composer --version',
        'git status',
        'git log --oneline -5'
    ];

    /**
     * Execute a predefined command
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function execute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'command' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validated = $validator->validated();

        $command = $validated['command'];

        // Periksa apakah perintah ada dalam daftar yang diizinkan
        if (!in_array($command, $this->allowedCommands)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Command not allowed',
                'allowed_commands' => $this->allowedCommands
            ], 403);
        }

        // Log command execution attempt
        Log::info('Command execution attempt', [
            'command' => $command,
            'ip' => $request->ip(),
            'timestamp' => Carbon::now()
        ]);

        try {
            // Eksekusi perintah dengan timeout
            $output = [];
            $returnCode = 0;

            // Set timeout to 60 seconds
            set_time_limit(60);

            // Execute command
            exec($command . ' 2>&1', $output, $returnCode);

            // Join output lines
            $outputString = implode("\n", $output);

            // Return hasil
            return response()->json([
                'status' => 'success',
                'command' => $command,
                'output' => $outputString,
                'return_code' => $returnCode
            ]);
        } catch (\Exception $e) {
            Log::error('Command execution failed', [
                'command' => $command,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'timestamp' => Carbon::now()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Command execution failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List allowed commands
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function commands()
    {
        return response()->json([
            'status' => 'success',
            'allowed_commands' => $this->allowedCommands
        ]);
    }
}
