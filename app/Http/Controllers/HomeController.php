<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::where('status', '1')->orderBy('name', 'ASC')->take(8)->get();

        $featured_jobs = Job::where('status',1)->where('isFeatured',1)->orderBy('created_at','DESC')->with('jobType')->take(6)->get();
        $latest_jobs = Job::where('status',1)->orderBy('created_at','DESC')->with('jobType')->take(6)->get();
        return view('front.home', [
            'categories' => $categories,
            'featured_jobs' => $featured_jobs,
            'latest_jobs' => $latest_jobs
        ]);
    }
}
