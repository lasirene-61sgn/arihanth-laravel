@extends('craftsman.layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Staff: {{ $staff->name }} ({{ $staff->staff_code }})</h1>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>- {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-200">
        <form action="{{ route('craftsman.staff.update', $staff->id) }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $staff->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Mobile *</label>
                    <input type="text" name="mobile" value="{{ old('mobile', $staff->mobile) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $staff->email) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Password (Leave blank to keep current)</label>
                    <input type="text" name="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Aadhar Number</label>
                    <input type="text" name="aadhar_number" value="{{ old('aadhar_number', $staff->aadhar_number) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <div class="mt-2">
                        @if($staff->is_active)
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Active
                            </span>
                            <input type="hidden" name="is_active" value="1">
                            <p class="text-xs text-red-500 mt-2"><i class="bi bi-info-circle"></i> Once active, a staff member cannot be deactivated.</p>
                        @else
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600">Activate Staff</span>
                            </label>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Profile Image</label>
                    @if($staff->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $staff->image) }}" class="h-16 w-16 object-cover rounded">
                        </div>
                    @endif
                    <input type="file" name="image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 p-2 border rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Aadhar Image</label>
                    @if($staff->aadhar_image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $staff->aadhar_image) }}" class="h-16 w-16 object-cover rounded">
                        </div>
                    @endif
                    <input type="file" name="aadhar_image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 p-2 border rounded-md">
                </div>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3 border-b pb-2">Permissions</h3>
                
                @php $perms = old('permissions', $staff->permissions ?? []); @endphp
                
                <div class="space-y-4">
                    <!-- Work Orders -->
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <h4 class="font-medium text-sm text-gray-800 mb-2">Work Orders</h4>
                        <div class="flex flex-wrap gap-4">
                            <label class="inline-flex items-center"><input type="checkbox" name="permissions[]" value="wo_view" {{ in_array('wo_view', $perms) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600"><span class="ml-2 text-sm text-gray-700">View</span></label>
                            <label class="inline-flex items-center"><input type="checkbox" name="permissions[]" value="wo_accept" {{ in_array('wo_accept', $perms) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600"><span class="ml-2 text-sm text-gray-700">Accept</span></label>
                            <label class="inline-flex items-center"><input type="checkbox" name="permissions[]" value="wo_reject" {{ in_array('wo_reject', $perms) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600"><span class="ml-2 text-sm text-gray-700">Reject</span></label>
                        </div>
                    </div>

                    <!-- Purchase Orders -->
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <h4 class="font-medium text-sm text-gray-800 mb-2">Purchase Orders</h4>
                        <div class="flex flex-wrap gap-4">
                            <label class="inline-flex items-center"><input type="checkbox" name="permissions[]" value="po_view" {{ in_array('po_view', $perms) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600"><span class="ml-2 text-sm text-gray-700">View</span></label>
                            <label class="inline-flex items-center"><input type="checkbox" name="permissions[]" value="po_accept" {{ in_array('po_accept', $perms) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600"><span class="ml-2 text-sm text-gray-700">Accept</span></label>
                            <label class="inline-flex items-center"><input type="checkbox" name="permissions[]" value="po_reject" {{ in_array('po_reject', $perms) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600"><span class="ml-2 text-sm text-gray-700">Reject</span></label>
                        </div>
                    </div>

                    <!-- Repairs -->
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <h4 class="font-medium text-sm text-gray-800 mb-2">Repairs</h4>
                        <div class="flex flex-wrap gap-4">
                            <label class="inline-flex items-center"><input type="checkbox" name="permissions[]" value="repair_view" {{ in_array('repair_view', $perms) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600"><span class="ml-2 text-sm text-gray-700">View</span></label>
                            <label class="inline-flex items-center"><input type="checkbox" name="permissions[]" value="repair_accept" {{ in_array('repair_accept', $perms) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600"><span class="ml-2 text-sm text-gray-700">Accept</span></label>
                            <label class="inline-flex items-center"><input type="checkbox" name="permissions[]" value="repair_reject" {{ in_array('repair_reject', $perms) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600"><span class="ml-2 text-sm text-gray-700">Reject</span></label>
                        </div>
                    </div>

                    <!-- Products -->
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <h4 class="font-medium text-sm text-gray-800 mb-2">Products</h4>
                        <div class="flex flex-wrap gap-4">
                            <label class="inline-flex items-center"><input type="checkbox" name="permissions[]" value="product_view" {{ in_array('product_view', $perms) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600"><span class="ml-2 text-sm text-gray-700">View</span></label>
                            <label class="inline-flex items-center"><input type="checkbox" name="permissions[]" value="product_create" {{ in_array('product_create', $perms) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600"><span class="ml-2 text-sm text-gray-700">Create</span></label>
                            <label class="inline-flex items-center"><input type="checkbox" name="permissions[]" value="product_edit" {{ in_array('product_edit', $perms) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600"><span class="ml-2 text-sm text-gray-700">Edit</span></label>
                        </div>
                    </div>

                    <!-- Design & Catalogue -->
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <h4 class="font-medium text-sm text-gray-800 mb-2">Design & Catalogue</h4>
                        <div class="flex flex-wrap gap-4">
                            <label class="inline-flex items-center"><input type="checkbox" name="permissions[]" value="design_view" {{ in_array('design_view', $perms) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600"><span class="ml-2 text-sm text-gray-700">Design (View)</span></label>
                            <label class="inline-flex items-center"><input type="checkbox" name="permissions[]" value="catalogue_view" {{ in_array('catalogue_view', $perms) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600"><span class="ml-2 text-sm text-gray-700">Catalogue (View)</span></label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-200">
                <a href="{{ route('craftsman.staff.index') }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md font-medium text-sm mr-3 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md font-medium text-sm">Update Staff</button>
            </div>
        </form>
    </div>
</div>
@endsection
