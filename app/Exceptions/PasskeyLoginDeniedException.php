<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class PasskeyLoginDeniedException extends Exception
{
    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 422);
        }

        return back()->withErrors(['credential' => $this->getMessage()]);
    }
}
