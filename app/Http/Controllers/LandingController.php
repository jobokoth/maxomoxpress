<?php

namespace App\Http\Controllers;

use App\Models\PlatformSetting;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $pricing = [
            [
                'name' => 'Starter',
                'price' => (int) PlatformSetting::get('tier1_price_kes', 1400),
                'students' => '≤ '.PlatformSetting::get('tier1_max_students', 100).' students',
                'color' => 'primary',
                'popular' => false,
                'features' => [
                    'Student records & lifecycle',
                    'Attendance tracking',
                    'Fee management & receipts',
                    'Mpesa & bank payments',
                    'Parent & student portal',
                    'Announcements & events',
                    'Basic reports',
                    '1-month free trial',
                ],
            ],
            [
                'name' => 'Growth',
                'price' => (int) PlatformSetting::get('tier2_price_kes', 3400),
                'students' => '101 – '.PlatformSetting::get('tier2_max_students', 400).' students',
                'color' => 'success',
                'popular' => true,
                'features' => [
                    'Everything in Starter',
                    'Timetable & gradebook',
                    'Library management',
                    'Transport & hostels',
                    'Clinic & discipline',
                    'QuickBooks sync',
                    'SMS notifications',
                    'Priority support',
                ],
            ],
            [
                'name' => 'Enterprise',
                'price' => (int) PlatformSetting::get('tier3_price_kes', 5400),
                'students' => '401+ students',
                'color' => 'dark',
                'popular' => false,
                'features' => [
                    'Everything in Growth',
                    'Multi-campus support',
                    'Advanced analytics',
                    'Alumni management',
                    'Custom branding',
                    'Dedicated onboarding',
                    'SLA guarantee',
                    'Phone support',
                ],
            ],
        ];

        return view('landing', compact('pricing'));
    }
}
