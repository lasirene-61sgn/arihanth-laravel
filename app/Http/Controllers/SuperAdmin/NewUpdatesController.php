<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\NewUpdates;
use Illuminate\Http\Request;

class NewUpdatesController extends Controller
{
    public function index(Request $request){
        $updates = NewUpdates::all();
        $editUpdate = null;

        if ($request->has('edit')) {
            $editUpdate = NewUpdates::find($request->edit);
        }
        
        $buyers = \App\Models\Buyer::select('id', 'name', 'bp_code')->get();
        $craftsmen = \App\Models\Craftman::select('id', 'name', 'craftman_code')->get();
        
        return view('super-admin.updates.index', compact('updates', 'editUpdate', 'buyers', 'craftsmen'));
    }

    public function create(Request $request)
    {
        return redirect()->route('super-admin.updates.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'newupdates' => 'nullable',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'duration' => 'nullable|integer',
            'target_audience' => 'nullable|in:all,buyer,craftsman',
            'target_buyers' => 'nullable|array',
            'target_craftsmen' => 'nullable|array',
            'media' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480',
        ]);

        $data = $request->only(['newupdates', 'title', 'description', 'duration', 'target_audience', 'target_buyers', 'target_craftsmen']);
        if (empty($data['target_audience'])) {
            $data['target_audience'] = 'all';
        }

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $extension = $file->getClientOriginalExtension();
            $mediaType = in_array(strtolower($extension), ['mp4', 'mov', 'avi']) ? 'video' : 'image';
            
            $path = $file->store('updates_media', 'public');
            
            $data['media_path'] = $path;
            $data['media_type'] = $mediaType;
        }

        NewUpdates::create($data);
        return redirect()->route('super-admin.updates.index')->with('success', 'Update created successfully');
    }

    public function edit($id)
    {
        $update = NewUpdates::findOrFail($id);
        $updates = NewUpdates::all();
        $editUpdate = $update;
        $buyers = \App\Models\Buyer::select('id', 'name', 'bp_code')->get();
        $craftsmen = \App\Models\Craftman::select('id', 'name', 'craftman_code')->get();
        
        return view('super-admin.updates.index', compact('update', 'updates', 'editUpdate', 'buyers', 'craftsmen'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'newupdates' => 'nullable',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'duration' => 'nullable|integer',
            'target_audience' => 'nullable|in:all,buyer,craftsman',
            'target_buyers' => 'nullable|array',
            'target_craftsmen' => 'nullable|array',
            'media' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480',
        ]);
        
        $update = NewUpdates::findOrFail($id);
        $data = $request->only(['newupdates', 'title', 'description', 'duration', 'target_audience', 'target_buyers', 'target_craftsmen']);
        if (empty($data['target_audience'])) {
            $data['target_audience'] = 'all';
        }

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $extension = $file->getClientOriginalExtension();
            $mediaType = in_array(strtolower($extension), ['mp4', 'mov', 'avi']) ? 'video' : 'image';
            
            $path = $file->store('updates_media', 'public');
            
            $data['media_path'] = $path;
            $data['media_type'] = $mediaType;
            
            // Optionally delete old media here if you want
        }

        $update->update($data);
        return redirect()->route('super-admin.updates.index')->with('success', 'Update updated successfully');
    }
    public function destroy($id)
    {
        $update = NewUpdates::findOrFail($id);
        $update->delete();
        return back()->with('success', 'Update deleted successfully');
    }
}
