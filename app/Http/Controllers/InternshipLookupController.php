<?php

namespace App\Http\Controllers;

use App\College;
use App\Stream;
use App\Subject;
use Illuminate\Http\Request;

/**
 * Public, read-only JSON for the cascading dropdowns on the registration form.
 * Returns only {id, name}.
 */
class InternshipLookupController extends Controller
{
    public function colleges(Request $request)
    {
        $request->validate(['university_id' => 'required|integer']);

        return $this->list(College::where('university_id', $request->university_id));
    }

    public function streams(Request $request)
    {
        $request->validate(['college_id' => 'required|integer']);

        return $this->list(Stream::where('college_id', $request->college_id));
    }

    public function subjects(Request $request)
    {
        $request->validate(['stream_id' => 'required|integer']);

        return $this->list(Subject::where('stream_id', $request->stream_id));
    }

    protected function list($query)
    {
        return response()->json($query->orderBy('name')->get(['id', 'name']));
    }
}
