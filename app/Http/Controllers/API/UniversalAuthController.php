<?php



namespace App\Http\Controllers\API;



use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Validator;

use App\Models\ProcessOwner;

use App\Models\Buyer;

use App\Models\Craftman;

use App\Models\KeyUser;

use App\Models\User;
use App\Models\RegistrationRequest;



class UniversalAuthController extends Controller

{

    /**

     * Universal Login API

     * Allows any user type (Super Admin, Admin, Buyer, Craftsman, Key User, End User)

     * to log in through a single endpoint.

     */

    public function login(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'email' => 'required|string', // can be email, mobile, or user code

            'password' => 'required|string',

            'fcm_token' => 'nullable|string',

        ]);



        if ($validator->fails()) {

            return response()->json([

                'success' => false,

                'message' => $validator->errors()->first(),

                'errors' => $validator->errors()

            ], 422);

        }



        $loginId = $request->email;

        $password = $request->password;



        // Default permissions as requested by user

        $defaultPermissions = [

            'products',

            'product',

            'design',

            'catalogue',

            'catelogur',

            'work_order',

            'workorders',

        ];



        // 1. Check Super Admins / Admins (ProcessOwner table)

        $superAdmin = ProcessOwner::where('email_id', $loginId)

            ->orWhere('mobile_no', $loginId)

            ->orWhere('user_code', $loginId)

            ->first();



        if ($superAdmin && Hash::check($password, $superAdmin->password)) {

            if ($request->filled('fcm_token')) {

                $superAdmin->update(['fcm_token' => $request->fcm_token]);

            }

            $token = $superAdmin->createToken('admin_token')->plainTextToken;



            // Merge permissions

            $existingPermissions = $superAdmin->permissions ?? [];

            if (is_string($existingPermissions)) {

                $existingPermissions = json_decode($existingPermissions, true) ?? [];

            }

            $superAdmin->permissions = array_merge($defaultPermissions, $existingPermissions);



            return response()->json([

                'success' => true,

                'message' => 'Login successful',

                'role' => $superAdmin->role, // 'super_admin' or 'admin'

                'token' => $token,

                'user' => $superAdmin

            ]);

        }



        // 2. Check Buyers

        $buyer = Buyer::where('email', $loginId)

            ->orWhere('mobile', $loginId)

            ->orWhere('bp_code', $loginId)

            ->first();



        if ($buyer && Hash::check($password, $buyer->password)) {

            if ($request->filled('fcm_token')) {

                $buyer->update(['fcm_token' => $request->fcm_token]);

            }

            $token = $buyer->createToken('buyer_token')->plainTextToken;



            // Merge permissions

            $existingPermissions = $buyer->permissions ?? [];

            if (is_string($existingPermissions)) {

                $existingPermissions = json_decode($existingPermissions, true) ?? [];

            }

            $buyer->permissions = array_merge($defaultPermissions, $existingPermissions);



            return response()->json([

                'success' => true,

                'message' => 'Login successful',

                'role' => 'buyer',

                'token' => $token,

                'user' => $buyer

            ]);

        }



        // 3. Check Craftsmen

        $craftsman = Craftman::where('email', $loginId)

            ->orWhere('mobile', $loginId)

            ->orWhere('craftman_code', $loginId)

            ->first();



        if ($craftsman && Hash::check($password, $craftsman->password)) {

            if ($request->filled('fcm_token')) {

                $craftsman->update(['fcm_token' => $request->fcm_token]);

            }

            $token = $craftsman->createToken('craftsman_token')->plainTextToken;



            // Merge permissions

            $existingPermissions = $craftsman->permissions ?? [];

            if (is_string($existingPermissions)) {

                $existingPermissions = json_decode($existingPermissions, true) ?? [];

            }

            $craftsman->permissions = array_merge($defaultPermissions, $existingPermissions);



            return response()->json([

                'success' => true,

                'message' => 'Login successful',

                'role' => 'craftsman',

                'token' => $token,

                'user' => $craftsman

            ]);

        }



        // 4. Check Key Users

        $keyUser = KeyUser::where('email_id', $loginId)

            ->orWhere('mobile_no', $loginId)

            ->orWhere('user_code', $loginId)

            ->first();



        if ($keyUser && Hash::check($password, $keyUser->password)) {

            if ($request->filled('fcm_token')) {

                $keyUser->update(['fcm_token' => $request->fcm_token]);

            }

            $token = $keyUser->createToken('keyuser_token')->plainTextToken;



            // Merge permissions

            $existingPermissions = $keyUser->permissions ?? [];

            if (is_string($existingPermissions)) {

                $existingPermissions = json_decode($existingPermissions, true) ?? [];

            }

            $keyUser->permissions = array_merge($defaultPermissions, $existingPermissions);



            return response()->json([

                'success' => true,

                'message' => 'Login successful',

                'role' => 'key_user',

                'token' => $token,

                'user' => $keyUser

            ]);

        }



        // 5. Check End Users

        $user = User::where('email', $loginId)

            ->orWhere('mobile_no', $loginId)

            ->orWhere('user_code', $loginId)

            ->first();



        if ($user && Hash::check($password, $user->password)) {

            if ($request->filled('fcm_token')) {

                $user->update(['fcm_token' => $request->fcm_token]);

            }

            $token = $user->createToken('user_token')->plainTextToken;



            // Merge permissions

            $existingPermissions = $user->permissions ?? [];

            if (is_string($existingPermissions)) {

                $existingPermissions = json_decode($existingPermissions, true) ?? [];

            }

            $user->permissions = array_merge($defaultPermissions, $existingPermissions);



            return response()->json([

                'success' => true,

                'message' => 'Login successful',

                'role' => 'user',

                'token' => $token,

                'user' => $user

            ]);

        }



        // If no match found

        return response()->json([

            'success' => false,

            'message' => 'Invalid credentials.'

        ], 401);

    }

    /**
     * Universal Registration API (creates a registration request)
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'mobile' => 'required|string|max:15',
            'business_name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'gst_no' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['password'] = Hash::make($request->password);

        $registration = RegistrationRequest::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Registration request submitted successfully! We will review and contact you soon.',
            'data' => $registration
        ]);
    }
}