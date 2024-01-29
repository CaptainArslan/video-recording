<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($request, $validator);
        }

        $status = Password::sendResetLink($request->only('email'));

        return $request->is('api/*')
            ? $this->apiResponse($status)
            : $this->webResponse($request, $status);
    }

    protected function validationErrorResponse(Request $request, $validator)
    {
        return $request->is('api/*')
            ? $this->respondWithError(implode(', ', $validator->errors()->all()))
            : back()->withErrors($validator)->withInput($request->only('email'));
    }

    protected function apiResponse($status)
    {
        return $status == Password::RESET_LINK_SENT
            ? $this->respondWithSuccess(null, 'Email sent successfully!')
            : $this->respondWithError(__('passwords.user'));
    }

    protected function webResponse(Request $request, $status)
    {
        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    }
}
