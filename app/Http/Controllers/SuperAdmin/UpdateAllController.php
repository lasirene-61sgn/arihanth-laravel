<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Updatesall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UpdateAllController extends Controller
{
    public function index()
    {
        $updates = Updatesall::latest()->paginate(10);
        return view('super-admin.update.index', compact('updates'));
    }

    public function create()
    {
        return view('super-admin.update.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:5048',
        ]);

        $imagePaths = [];

        if($request->hasFile('images'))
            {
                foreach($request->file('images') as $image){
                    $path = $image->store('updates', 'public');
                    $imagePaths[] = $path;
                }
            }
        Updatesall::create([
            'title' => $request->title,
            'description' => $request->description,
            'images' => json_encode($imagePaths),
        ]);
        return redirect()->route('super-admin.update.index')->with('success', 'Updates Created Successfully');
    }

    public function edit($id)
    {
        $update = Updatesall::findOrFail($id);
        return view('super-admin.update.edit', compact('update'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'images' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:5048',
        ]);

        $update = Updatesall::findOrFail($id);
        $imagePaths = json_decode($update->image, true) ?? [];

        if($request->hasFile('images')){
            foreach($request->file('images') as $image){
                $path = $image->store('updates', 'public');
                $imagePaths[] = $path;
            }
        }
        $update->update([
            'title' => $request->title,
            'description' => $request->description,
            'images' => json_encode($imagePaths),
        ]);
        return redirect()->route('super-admin.update.index')->with('Success', 'Update Successfully');
    }

    public function destroy($id)
    {
        $update = Updatesall::findOrFail($id);

        $imagePaths = json_decode($update->images, true) ?? [];
        foreach($imagePaths as $image){
            Storage::disk('public')->delete('$image');
        }
        $update->delete();
        return redirect()->route('super-admin.update.index')->with('success', 'update Deleted Successfully');
    }
}
