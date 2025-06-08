<?php

namespace App\Http\Controllers;

use App\Models\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MaintenanceController extends Controller
{
    public function show()
    {
        $siteFlags = SiteSettings::find(1);

        if (Session::has('maintenance_password')) {
            return redirect()->route('welcome.page')->with(['success' => 'Already authenticated.']);
        }
        if ($siteFlags->site_maintenance != true) {
            return redirect()->route('welcome.page');
        }

        return inertia('Maintenance/Index', [
            'disclaimer' => app()->environment() === 'local' ? "The Maintenance Password is " . config('app.maintenance_password') : "Contact a developer for access.",
        ]);
    }

    public function authenticate(Request $request)
    {
        $maintenancePassword = config('app.maintenance_password');

        if (Session::has('maintenance_password')) {
            return response()->json(['error' => 'Already authenticated.']);
        }

        $password = $request->input('password');

        if (empty($password)) {
            return response()->json(['error' => 'Please provide a password.']);
        }

        if ($password != $maintenancePassword) {
            return response()->json(['error' => 'Invalid password.']);
        }

        Session::put('maintenance_password', $password);

        return redirect(request()->url());
    }

    public function Exit()
    {
        session()->forget('maintenance_password');

        return inertia()->location(route('maintenance.page'));
    }
}
