<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LogoutResponse implements LogoutResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        return $request->wantsJson()
            ? response()->json(['message' => 'Logged out successfully'])
            : redirect()->route('login');
    }
}
