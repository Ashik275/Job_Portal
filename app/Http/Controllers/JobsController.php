<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use Illuminate\Http\Request;

class JobsController extends Controller
{
    ## this will show jobs page
    public function index(Request $request)
    {

        $categories = Category::where('status', 1)->get();
        $jobTypes = JobType::where('status', 1)->get();

        $jobs = Job::where('status', 1);

        // search using keyword
        if (!empty($request->keyword)) {
            $jobs = $jobs->where(function ($query) use ($request) {
                $query->orWhere('title', 'like', '%' . $request->keyword . '%');
                $query->orWhere('keywords', 'like', '%' . $request->keyword . '%');
            });
        }
        // search using locatin
        if (!empty($request->location)) {
            $jobs = $jobs->where('location', $request->location);
        }
        // search using category
        if (!empty($request->category)) {
            $jobs = $jobs->where('category_id', $request->category);
        }
        // search using job type
        $jobTypeArray = [];
        if (!empty($request->job_type)) {
            $jobTypeArray = explode(',', $request->job_type);
            $jobs = $jobs->whereIn('job_type_id', $jobTypeArray);
        }

        // search using experience
        if (!empty($request->experience)) {
            $jobs = $jobs->where('experience', $request->experience);
        }
        $jobs = $jobs->with(['jobType', 'category']);
        if ($request->sort == '0') {
            $jobs = $jobs ->orderBy('created_at', 'ASC');
        }else{
            $jobs = $jobs ->orderBy('created_at', 'DESC');
        }
        $jobs = $jobs->paginate(9);
        return view('front.jobs', [
            'jobTypes' => $jobTypes,
            'jobs' => $jobs,
            'jobTypeArray' => $jobTypeArray,
            'categories' => $categories
        ]);
    }

    // this method will show job detail page
    public function detail($id){

        $job = Job::where(['id' => $id, 'status' => 1])->with(['jobType','category'])->first();
        
        if($job == null){
            abort(404);
        }
        return view('front.jobDetail',[
            'job' =>$job
        ]);
    }
}
