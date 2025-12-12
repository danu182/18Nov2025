<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage; // Pastikan ini ada

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Eager Load relasi: company, department, dan roles
            $query = User::with(['company', 'department', 'roles'])
                          ->select('users.*');

            return DataTables::of($query)
                ->addColumn('avatar_display', function($user) {
                    // Path ke avatar yang diunggah. $user->avatar berisi 'avatars/namafile.jpg'
                    $avatarUrl = $user->avatar 
                        ? asset('storage/' . $user->avatar) // Menggunakan asset('storage/' . $path_relatif)
                        // Path ke avatar default
                        : asset('assets/img/avatar/Header-avatar-01.jpg'); 

                    // Pastikan class 'rounded-circle' atau 'img-avatar' ada CSS-nya
                    return '<img src="'.$avatarUrl.'" alt="'.$user->name.'" width="50" class="rounded-circle">';
                })
                
                ->addColumn('role_name', function ($user) {
                    return $user->roles->first()->name ?? '-';
                })
                
                ->addColumn('company_name', function ($user) {
                    return $user->company->name ?? '-';
                })
                
                ->addColumn('department_name', function ($user) {
                    return $user->department->name ?? '-';
                })
                
                ->addColumn('action', function ($user) {
                    $editUrl = route('users.edit', $user->id);
                    $showUrl = route('users.show', $user->id);
                    $deleteUrl = route('users.destroy', $user->id);

                    return '
                        <a href="' . $showUrl . '" class="btn btn-info btn-sm me-1">Detail</a> 
                        <a href="' . $editUrl . '" class="btn btn-primary btn-sm me-1">Edit</a>
                        <form action="' . $deleteUrl . '" method="POST" class="d-inline" id="deleteUserForm' . $user->id . '">
                            ' . method_field('delete') . csrf_field() . '
                            <button type="button" 
                                    class="btn btn-danger btn-sm delete-confirmation" 
                                    data-id="' . $user->id . '"
                                    data-name="' . $user->name . '"
                            >
                                Hapus
                            </button>
                        </form>
                    ';
                })
                
                // PENTING: Tambahkan kolom yang berisi HTML (avatar_display)
                ->rawColumns(['avatar_display', 'action']) 
                ->make(true);
        }

        return view('backend.user.index', [
            'title' => 'Manajemen Pengguna',
            'subtitle' => 'Data Pengguna',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('backend.user.create', [
            'roles' => $roles,
            'companies' => $companies,
            'departments' => $departments,
            'title' => 'Manajemen Pengguna',
            'subtitle' => 'Tambah Pengguna Baru',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed', 
            'role_id' => 'required|exists:roles,id',
            'company_id' => 'nullable|exists:companies,id', 
            'department_id' => 'nullable|exists:departments,id', 
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', 
            'signature' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        // --- START: Penanganan File Avatar dan Signature ---
        
        $avatarPath = null;
        $signaturePath = null;

        // 1A. Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            // $avatarPath akan berisi path relatif dari storage/app/public (e.g., 'avatars/namafile.jpg')
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        // 1B. Handle Signature Upload
        if ($request->hasFile('signature')) {
            $signaturePath = $request->file('signature')->store('signatures', 'public');
        }
        
        // --- END: Penanganan File Avatar dan Signature ---

        // 2. Hash Password dan Create User
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'company_id' => $validated['company_id'], 
            'department_id' => $validated['department_id'],
            // Simpan path yang didapatkan dari proses upload ke kolom 'avatar'
            'avatar' => $avatarPath, 
            'signature' => $signaturePath,
        ]);
        
        // 3. Simpan Role menggunakan Spatie
        $role = Role::findById($validated['role_id']); 
        $user->syncRoles($role);
        
        // 4. Redirect
        return redirect()->route('users.index')->with('success', 'Pengguna **' . $user->name . '** berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('company', 'department'); 

        $roles = $user->getRoleNames(); 
        $permissions = $user->getAllPermissions(); 

        return view('backend.user.show', [
            'user' => $user, 
            'roles' => $roles,
            'permissions' => $permissions,
            'title' => 'Manajemen Pengguna',
            'subtitle' => 'Detail Pengguna: ' . $user->name,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $companies =Company::orderBy('name')->get();
        $departments =Department::orderBy('name')->get();
        $currentRoleId = optional($user->roles->first())->id;

        return view('backend.user.edit', [
            'user' => $user, 
            'roles' => $roles,
            'companies' => $companies,
            'departments' => $departments,
            'title' => 'Manajemen Pengguna',
            'subtitle' => 'Edit Pengguna: ' . $user->name,
            'currentRoleId'=> $currentRoleId,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // 1. VALIDASI DATA
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'avatar' => 'nullable|image|mimes:jpg,png,jpeg|max:2048', 
            'signature' => 'nullable|image|mimes:png,jpg,jpeg|max:1024', 
            'company_id' => 'nullable|exists:companies,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);
        
        // Ambil semua input kecuali file dan password_confirmation
        $data = $request->except(['_token', '_method', 'password', 'password_confirmation', 'avatar', 'signature']);

        // 2. PROSES UPLOAD & PENGHAPUSAN AVATAR
        if ($request->hasFile('avatar')) {
            // Hapus file lama jika ada (MENGGUNAKAN $user->avatar)
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            // Simpan file baru dan dapatkan path relatif
            // Path akan disimpan di kolom 'avatar' di DB.
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            
            // Simpan path relatif ke array data untuk update
            $data['avatar'] = $avatarPath; // PERBAIKAN: Menggunakan 'avatar' bukan 'avatar_path'
        }

        // 3. PROSES UPLOAD & PENGHAPUSAN SIGNATURE
        if ($request->hasFile('signature')) {
            // Hapus file lama jika ada (MENGGUNAKAN $user->signature)
            if ($user->signature && Storage::disk('public')->exists($user->signature)) {
                Storage::disk('public')->delete($user->signature);
            }
            
            // Simpan file baru dan dapatkan path relatif
            $signaturePath = $request->file('signature')->store('signatures', 'public');
            
            // Simpan path relatif ke array data untuk update
            $data['signature'] = $signaturePath; // PERBAIKAN: Menggunakan 'signature' bukan 'signature_path'
        }
        
        // 4. PROSES PASSWORD (Jika diisi)
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        // 5. UPDATE DATA PENGGUNA
        $user->update($data);

        // 5.b. UPDATE ROLE (untuk Spatie)
        if ($request->filled('role_id')) {
            $roleName = Role::findById($request->role_id)->name;
            $user->syncRoles($roleName); 
        }

        // 6. REDIREKSI
        return redirect()->route('users.index')->with('success', 'Pengguna **' . $user->name . '** berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // 1. Hapus File Avatar dan Signature dari Storage
        
        // Hapus Avatar
        if ($user->avatar) {
            $isDeleted = Storage::disk('public')->delete($user->avatar);
            if ($isDeleted) {
                Log::info('Avatar file deleted successfully for user ID: ' . $user->id);
            } else {
                Log::warning('Failed to delete avatar file for user ID: ' . $user->id . ' at path: ' . $user->avatar);
            }
        }

        // Hapus Signature
        if ($user->signature) { // Menggunakan $user->signature
            $isDeleted = Storage::disk('public')->delete($user->signature);
            if ($isDeleted) {
                Log::info('Signature file deleted successfully for user ID: ' . $user->id);
            } else {
                Log::warning('Failed to delete signature file for user ID: ' . $user->id . ' at path: ' . $user->signature);
            }
        }
        
        // 2. Simpan Nama Pengguna untuk Notifikasi
        $userName = $user->name;

        // 3. Hapus Data Pengguna dari Database
        $user->delete();

        // 4. Redirect dengan Notifikasi Sukses
        return redirect()->route('users.index')->with('success', 'Pengguna **' . $userName . '** dan data terkait berhasil dihapus!');
    }
}