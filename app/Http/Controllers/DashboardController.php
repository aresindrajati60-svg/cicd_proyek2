<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Destinasi;
use App\Models\User;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
  public function index()
{
    $bulanIni = now()->month;
    $tahunIni = now()->year;

    // ================= SUPERADMIN =================
    if (Auth::guard('superadmin')->check()) {

        $totalDestinasi = Destinasi::count();
        $totalUsers = User::count();

        $transaksiBulanIni = DB::table('pemesanan')
            ->whereMonth('tanggal_pemesanan', $bulanIni)
            ->whereYear('tanggal_pemesanan', $tahunIni)
            ->count();

        $pendapatanBulanIni = DB::table('pembayaran')
            ->whereMonth('tanggal_bayar', $bulanIni)
            ->whereYear('tanggal_bayar', $tahunIni)
            ->sum('total_bayar');
    }

    // ================= ADMIN =================
    else {

        $adminId = Auth::guard('web')->id();

        $totalDestinasi = Destinasi::where('created_by_id', $adminId)->count();
        $totalUsers = null;

        // ✅ TAMBAH JOIN + FILTER (tanpa ubah struktur)
        $transaksiBulanIni = DB::table('pemesanan as p')
            ->join('destinasi as d', 'p.id_destinasi', '=', 'd.id_destinasi')
            ->where('d.created_by_id', $adminId)
            ->whereBetween('p.tanggal_pemesanan', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])
            ->count();

        // ✅ TAMBAH JOIN + FILTER
        $pendapatanBulanIni = DB::table('pembayaran as pay')
            ->join('pemesanan as p', 'pay.id_pemesanan', '=', 'p.id_pemesanan')
            ->join('destinasi as d', 'p.id_destinasi', '=', 'd.id_destinasi')
            ->where('d.created_by_id', $adminId)
            ->whereMonth('pay.tanggal_bayar', $bulanIni)
            ->whereYear('pay.tanggal_bayar', $tahunIni)
            ->sum('total_bayar');
    }

    $totalTransaksi = $transaksiBulanIni;
    $pendapatan = $pendapatanBulanIni;

    $activities = ActivityLog::latest()->limit(5)->get();

    return view('dashboard', compact(
        'totalDestinasi',
        'totalUsers',
        'totalTransaksi',
        'pendapatan',
        'transaksiBulanIni',
        'pendapatanBulanIni',
        'activities'
    ));
}



    public function destinasi()
    {
        return view('destinasi.index'); 
    }

    public function detail($id)
    {
        return view('destinasi.detail', compact('id'));
    }

    public function rekomendasi()
    {
        return view('rekomendasi.index');
    }

    public function users()
    {
        return view('user.index');
    }

    public function transaksi()
{
    $isSuperAdmin = Auth::guard('superadmin')->check();
    $adminId = Auth::guard('web')->id();

    // ================= SUMMARY =================
    $summaryRevenue = DB::table('pembayaran as pay')
        ->join('pemesanan as p','pay.id_pemesanan','=','p.id_pemesanan')
        ->join('destinasi as d','p.id_destinasi','=','d.id_destinasi');

    if (!$isSuperAdmin) {
        $summaryRevenue->where('d.created_by_id', $adminId);
    }

    $summaryPengunjung = DB::table('pemesanan as p')
        ->join('destinasi as d','p.id_destinasi','=','d.id_destinasi');

    if (!$isSuperAdmin) {
        $summaryPengunjung->where('d.created_by_id', $adminId);
    }

    $summaryTransaksi = DB::table('pemesanan as p')
        ->join('destinasi as d','p.id_destinasi','=','d.id_destinasi');

    if (!$isSuperAdmin) {
        $summaryTransaksi->where('d.created_by_id', $adminId);
    }

    $summaryPending = DB::table('pemesanan as p')
        ->join('destinasi as d','p.id_destinasi','=','d.id_destinasi')
        ->where('p.status','pending');

    if (!$isSuperAdmin) {
        $summaryPending->where('d.created_by_id', $adminId);
    }

    $summary = [
        'revenue' => $summaryRevenue->sum('total_bayar'),
        'pengunjung' => $summaryPengunjung->sum('jumlah_tiket'),
        'transaksi' => $summaryTransaksi->count(),
        'pending' => $summaryPending->count(),
    ];

    // ================= TRANSAKSI =================
    $transactions = DB::table('pemesanan as p')
        ->join('users as u', 'p.user_uuid', '=', 'u.id')
        ->join('destinasi as d','p.id_destinasi','=','d.id_destinasi')
        ->leftJoin('pembayaran as pay','pay.id_pemesanan','=','p.id_pemesanan');

    if (!$isSuperAdmin) {
        $transactions->where('d.created_by_id', $adminId);
    }

    $transactions = $transactions
        ->select(
            'p.id_pemesanan as kode',
            'p.tanggal_pemesanan as tanggal',
            'u.name as customer',
            'd.nama as lokasi',
            DB::raw("'Reguler Dewasa' as tiket"),
            'p.jumlah_tiket as jumlah',
            'pay.metode_bayar as pembayaran',
            'p.total_harga as total',
            'pay.status_pembayaran as status'
        )
        ->orderBy('p.tanggal_pemesanan','desc')
        ->get();

    // ================= TREND =================
    $trend = $transactions
        ->groupBy(function ($t) {
            return date('Y-m-d', strtotime($t->tanggal));
        })
        ->map(function ($items, $tanggal) {
            return [
                'tanggal' => $tanggal,
                'revenue' => $items->sum('total'),
                'pengunjung' => $items->sum('jumlah')
            ];
        })
        ->values();

    // ================= METODE =================
    $metodePembayaran = $transactions
        ->groupBy(function ($t) {
            return $t->pembayaran ?? 'Lainnya';
        })
        ->map(function ($items, $metode) {
            return [
                'metode' => $metode,
                'total' => $items->count()
            ];
        })
        ->values();

    return view('transaksi.index', [
        'summary' => $summary,
        'transactions' => $transactions,
        'trend' => $trend,
        'metodePembayaran' => $metodePembayaran
    ]);
}

    public function akun()
    {
        return view('user.akun');
    }

    public function adminPengaturan()
    {
        if (auth()->guard('superadmin')->check()) {
            $user = auth()->guard('superadmin')->user();
        } else {
            $user = auth()->guard('web')->user();
        }

        return view('admin.settings', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name'  => 'required',
            'email'      => 'required|email',
            'photo'      => 'nullable|image|mimes:jpg,png,jpeg|max:2048'
        ]);

        if (auth()->guard('superadmin')->check()) {
            $user = auth()->guard('superadmin')->user();
        } else {
            $user = auth()->guard('web')->user();
        }

        if ($request->hasFile('photo')) {
            if ($user->photo && file_exists(public_path($user->photo))) {
                unlink(public_path($user->photo));
            }

            $file = $request->file('photo');
            $filename = time().'.'.$file->getClientOriginalExtension();

            $file->move(public_path('uploads/profile'), $filename);

            $user->photo = 'uploads/profile/'.$filename;
        }

        $user->first_name = $request->first_name;
        $user->last_name  = $request->last_name;
        $user->email      = $request->email;
        $user->phone      = $request->phone;
        $user->bio        = $request->bio;
        $user->location   = $request->location;

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if (auth()->guard('superadmin')->check()) {
            $user = auth()->guard('superadmin')->user();
        } else {
            $user = auth()->guard('web')->user();
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Password saat ini salah!');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password berhasil diubah!');
    }

    public function superadminPengaturan(Request $request)
    {
        if (!auth()->guard('superadmin')->check()) {
            abort(403);
        }

        $role = $request->role ?? 'admin';

        $data = DB::table('hak_akses')->where('role', $role)->first();
        $permissions = $data ? json_decode($data->permissions, true) : [];

        return view('superadmin.settings', [
            'user' => auth()->guard('superadmin')->user(),
            'role' => $role,
            'permissions' => $permissions
        ]);
    }

    public function updateAkses(Request $request)
    {
        if (!auth()->guard('superadmin')->check()) {
            abort(403);
        }

        DB::table('hak_akses')->updateOrInsert(
            ['role' => $request->role],
            ['permissions' => json_encode($request->permissions ?? [])]
        );

        return back()->with('success', 'Hak akses berhasil diperbarui!');
    }
}