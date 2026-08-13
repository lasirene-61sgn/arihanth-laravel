<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function currentCraftsman()
    {
        if (auth()->guard('craftsman')->check()) {
            return auth()->guard('craftsman')->user();
        } elseif (auth()->guard('craftsman_staff')->check()) {
            return auth()->guard('craftsman_staff')->user()->craftsman;
        }
        return null;
    }

    protected function currentStaff()
    {
        if (auth()->guard('craftsman_staff')->check()) {
            return auth()->guard('craftsman_staff')->user();
        }
        return null;
    }
}
