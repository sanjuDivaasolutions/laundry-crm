<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;

class SubscribeController extends Controller
{
    public function index(Company $company)
    {
        $subscriptionUrl = route('api.newsletter-subscribe-post');

        return view('newsletter.subscribe', compact('company', 'subscriptionUrl'));
    }
}
