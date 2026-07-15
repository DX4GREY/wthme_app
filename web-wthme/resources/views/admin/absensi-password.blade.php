@extends('layouts.app')

@section('title', 'Manajemen Password Absensi Harian')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Password Absensi Harian
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Buat dan kelola password akses data absensi panitia
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('admin.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Buat Password -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            @if($todayPassword)
                                Update Password Hari Ini
                            @else
                                Buat Password Hari Ini
                            @endif
                        </h3>

                        @if($todayPassword)
                            <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                <p class="text-sm text-yellow-700">
                                    <strong>Password saat ini:</strong> 
                                    <span class="font-mono bg-yellow-100 px-2 py-1 rounded">{{ $todayPassword->password_tampil ?? '***' }}</span>
                                </p>
                                <p class="text-xs text-yellow-600 mt-1">
                                    Dibuat: {{ $todayPassword->dibuat_pada ? $todayPassword->dibuat_pada->format('d/m/Y H:i') : '-' }}
                                </p>
                            </div>
                        @else
                            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                <p class="text-sm text-blue-700">
                                    Belum ada password untuk hari ini. Buat password sekarang.
                                </p>
                            </div>
                        @endif

                        <form action="{{ route('admin.absensi.password.store') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                    Password Baru
                                </label>
                                <input 
                                    type="text" 
                                    name="password" 
                                    id="password" 
                                    required 
                                    class="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 focus:z-10 sm:text-sm"
                                    placeholder="Masukkan password harian"
                                    autofocus
                                >
                                <p class="mt-1 text-xs text-gray-500">
                                    Minimal 6 karakter, maksimal 50 karakter
                                </p>
                            </div>

                            <button 
                                type="submit" 
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                            >
                                @if($todayPassword)
                                    Update Password
                                @else
                                    Buat Password
                                @endif
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zm-1 9a1 1 0 01-1-1v-1a1 1 0 112 0v1a1 1 0 01-1 1zm0-4a1 1 0 01-1-1V8a1 1 0 112 0v2a1 1 0 01-1 1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Informasi</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Password berubah setiap hari</li>
                                    <li>Hanya admin yang dapat membuat password</li>
                                    <li>Panitia diminta password untuk akses data absensi</li>
                                    <li>Password disimpan dalam bentuk terenkripsi</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Password -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            7 Password Terakhir
                        </h3>

                        @if($recentPasswords->count() > 0)
                            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                                Tanggal
                                            </th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                Dibuat Oleh
                                            </th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                Waktu Pembuatan
                                            </th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @foreach($recentPasswords as $password)
                                            <tr class="{{ $password->tanggal == date('Y-m-d') ? 'bg-yellow-50' : '' }}">
                                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                    {{ \Carbon\Carbon::parse($password->tanggal)->format('d/m/Y') }}
                                                    @if($password->tanggal == date('Y-m-d'))
                                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            Hari Ini
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                    {{ $password->dibuatOleh->name ?? 'System' }}
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                    {{ $password->dibuat_pada ? $password->dibuat_pada->format('d/m/Y H:i') : '-' }}
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                    @if($password->tanggal == date('Y-m-d'))
                                                        <span class="inline-flex items-center text-green-600">
                                                            <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                            </svg>
                                                            Aktif
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400">Expired</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">Belum ada password yang dibuat.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection