<?php

namespace App\Http\Controllers;

use App\Mail\JobNotificationEmail;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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
            $jobs = $jobs->orderBy('created_at', 'ASC');
        } else {
            $jobs = $jobs->orderBy('created_at', 'DESC');
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
    public function detail($id)
    {

        $job = Job::where(['id' => $id, 'status' => 1])->with(['jobType', 'category'])->first();
        $count = SavedJob::where([
            'user_id' => Auth::user()->id,
            'job_id' => $id
        ])->count();
        if ($job == null) {
            abort(404);
        }
        return view('front.jobDetail', [
            'job' => $job,
            'count' => $count
        ]);
    }

    public function applyJob(Request $request)
    {
        $id = $request->id;

        $job = Job::where('id', $id)->first();

        // If job not found in db
        if ($job == null) {
            session()->flash('error', 'Job does not exists');
            return response()->json([
                'status' => false,
                'message' => 'Job does not exists'
            ]);
        }

        // you can not apply in your own job
        $employer_id = $job->user_id;

        if ($employer_id == Auth::user()->id) {
            session()->flash('error', 'You can not apply in your own job');
            return response()->json([
                'status' => false,
                'message' => 'You can not apply in your own job'
            ]);
        }

        // you can not apply a job twice
        $jobapplication = JobApplication::where([
            'user_id' => Auth::user()->id,
            'job_id' => $id
        ])->count();

        if ($jobapplication > 0) {
            session()->flash('error', 'You already applied this job');
            return response()->json([
                'status' => false,
                'message' => 'You already applied this job'
            ]);
        }
        $application = new JobApplication();
        $application->job_id = $id;
        $application->user_id = Auth::user()->id;
        $application->employer_id = $employer_id;
        $application->applied_date = now();
        $application->save();

        // send notfication email to employer
        $employer = User::where('id', $employer_id)->first();
        $mailData = [
            'employer' => $employer,
            'user' => Auth::user(),
            'job' => $job
        ];
        Mail::to($employer->email)->send(new JobNotificationEmail($mailData));

        session()->flash('success', 'You have successfully applied');
        return response()->json([
            'status' => true,
            'message' => 'You have successfully applied'
        ]);
    }

    public function saveJob(Request $request)
    {
        $id = $request->id;

        $job = Job::find($id);

        if ($job  == null) {
            session()->flash('error', 'Job does not exists');
            return response()->json([
                'status' => false,
                'message' => 'Job does not exists'
            ]);
        }
        // Check if user already saved the job

        $count = SavedJob::where([
            'user_id' => Auth::user()->id,
            'job_id' => $id
        ])->count();

        if ($count > 0) {
            session()->flash('error', 'You already saved this job');
            return response()->json([
                'status' => false,
                'message' => 'You already saved this job'
            ]);
        }

        $savedJob = new SavedJob;
        $savedJob->job_id = $id;
        $savedJob->user_id = Auth::user()->id;
        $savedJob->save();
        session()->flash('success', 'You successfully saved this job');
        return response()->json([
            'status' => true,
            'message' => 'You successfully saved this job'
        ]);

    }
}
