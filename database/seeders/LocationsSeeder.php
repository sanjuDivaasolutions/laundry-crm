<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationsSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        City::truncate();
        State::truncate();
        Country::truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Seed Countries
        $countries = [
            ['name' => 'India', 'active' => true],
            ['name' => 'United States', 'active' => true],
            ['name' => 'Canada', 'active' => true],
            ['name' => 'United Kingdom', 'active' => true],
            ['name' => 'Australia', 'active' => true],
            ['name' => 'United Arab Emirates', 'active' => true],
        ];

        foreach ($countries as $c) {
            Country::create($c);
        }

        // 2. Seed States (India)
        $india = Country::where('name', 'India')->first();
        if ($india) {
            $states = [
                'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
                'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand',
                'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur',
                'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
                'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
                'Uttar Pradesh', 'Uttarakhand', 'West Bengal', 'Delhi'
            ];
            foreach ($states as $name) {
                State::create(['name' => $name, 'country_id' => $india->id, 'active' => true]);
            }
        }

        // 3. Seed States (USA)
        $usa = Country::where('name', 'United States')->first();
        if ($usa) {
            $states = [
                'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado',
                'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho',
                'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana',
                'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi',
                'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey',
                'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma',
                'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota',
                'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington',
                'West Virginia', 'Wisconsin', 'Wyoming'
            ];
            foreach ($states as $name) {
                State::create(['name' => $name, 'country_id' => $usa->id, 'active' => true]);
            }
        }

        // 4. Seed Cities (Sample for Gujarat, India)
        $gujarat = State::where('name', 'Gujarat')->first();
        if ($gujarat) {
            $cities = [
                'Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar', 'Jamnagar',
                'Junagadh', 'Gandhinagar', 'Anand', 'Navsari', 'Morbi', 'Nadiad',
                'Surendranagar', 'Bharuch', 'Mehsana', 'Bhuj', 'Porbandar', 'Palanpur',
                'Valsad', 'Vapi', 'Gondal', 'Veraval', 'Godhra', 'Patan', 'Kalol'
            ];
            foreach ($cities as $name) {
                City::create(['name' => $name, 'state_id' => $gujarat->id, 'active' => true]);
            }
        }
        
        // 4.1 Seed Cities (Sample for Maharashtra, India)
        $maharashtra = State::where('name', 'Maharashtra')->first();
        if ($maharashtra) {
            $cities = [
                'Mumbai', 'Pune', 'Nagpur', 'Thane', 'Nashik', 'Kalyan-Dombivli',
                'Vasai-Virar', 'Aurangabad', 'Navi Mumbai', 'Solapur', 'Mira-Bhayandar',
                'Bhiwandi', 'Amravati', 'Nanded', 'Kolhapur'
            ];
            foreach ($cities as $name) {
                City::create(['name' => $name, 'state_id' => $maharashtra->id, 'active' => true]);
            }
        }
        
        // 4.2 Seed Cities (Sample for California, USA)
        $california = State::where('name', 'California')->first();
        if ($california) {
            $cities = [
                'Los Angeles', 'San Diego', 'San Jose', 'San Francisco', 'Fresno',
                'Sacramento', 'Long Beach', 'Oakland', 'Bakersfield', 'Anaheim'
            ];
            foreach ($cities as $name) {
                City::create(['name' => $name, 'state_id' => $california->id, 'active' => true]);
            }
        }
        
        // 4.3 Seed Cities (Sample for New York, USA)
        $ny = State::where('name', 'New York')->first();
        if ($ny) {
             $cities = [
                'New York City', 'Buffalo', 'Rochester', 'Yonkers', 'Syracuse', 'Albany'
            ];
            foreach ($cities as $name) {
                City::create(['name' => $name, 'state_id' => $ny->id, 'active' => true]);
            }
        }

        // 5. Seed Provinces (Canada)
        $canada = Country::where('name', 'Canada')->first();
        if ($canada) {
            $provinces = [
                'Alberta', 'British Columbia', 'Manitoba', 'New Brunswick', 'Newfoundland and Labrador',
                'Nova Scotia', 'Ontario', 'Prince Edward Island', 'Quebec', 'Saskatchewan',
                'Northwest Territories', 'Nunavut', 'Yukon'
            ];
            foreach ($provinces as $name) {
                State::create(['name' => $name, 'country_id' => $canada->id, 'active' => true]);
            }

            // 5.1 Seed Cities (Ontario)
            $ontario = State::where('name', 'Ontario')->where('country_id', $canada->id)->first();
            if ($ontario) {
                $cities = [
                    'Toronto', 'Ottawa', 'Mississauga', 'Brampton', 'Hamilton', 'London', 
                    'Markham', 'Vaughan', 'Kitchener', 'Windsor'
                ];
                foreach ($cities as $name) {
                    City::create(['name' => $name, 'state_id' => $ontario->id, 'active' => true]);
                }
            }

            // 5.2 Seed Cities (British Columbia)
            $bc = State::where('name', 'British Columbia')->where('country_id', $canada->id)->first();
            if ($bc) {
                $cities = [
                    'Vancouver', 'Surrey', 'Burnaby', 'Richmond', 'Abbotsford', 'Coquitlam', 
                    'Kelowna', 'Kamloops', 'Nanaimo', 'Victoria'
                ];
                foreach ($cities as $name) {
                    City::create(['name' => $name, 'state_id' => $bc->id, 'active' => true]);
                }
            }

            // 5.3 Seed Cities (Quebec)
            $quebec = State::where('name', 'Quebec')->where('country_id', $canada->id)->first();
            if ($quebec) {
                $cities = [
                    'Montreal', 'Quebec City', 'Laval', 'Gatineau', 'Longueuil', 
                    'Sherbrooke', 'Saguenay', 'Levis'
                ];
                foreach ($cities as $name) {
                    City::create(['name' => $name, 'state_id' => $quebec->id, 'active' => true]);
                }
            }

            // 5.4 Seed Cities (Alberta)
            $alberta = State::where('name', 'Alberta')->where('country_id', $canada->id)->first();
            if ($alberta) {
                $cities = [
                    'Calgary', 'Edmonton', 'Red Deer', 'Lethbridge', 'St. Albert'
                ];
                foreach ($cities as $name) {
                    City::create(['name' => $name, 'state_id' => $alberta->id, 'active' => true]);
                }
            }
        }
    }
}
