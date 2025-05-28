<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function TermsOfService()
    {
        return inertia('App/Policies/TermsOfService');
    }
     public function PrivacyPolicy()
    {
        return inertia('App/Policies/Privacy');
    }
}
