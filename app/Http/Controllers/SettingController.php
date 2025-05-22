<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function account()
    {
        return Inertia::render('Setting/Account', [
            'user' => auth()->user()
        ]);
    }
    public function changePassword()
    {
        return Inertia::render('Setting/ChangePassword');
    }
}
