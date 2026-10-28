
@extends('layouts.admin')

@section('title', $category->exists ? 'Edit Kategori' : 'Tambah Kategori')
@section('breadcrumb', $category->exists ? 'Edit Kategori' : 'Tambah Kategori')
@section('body-class', 'admin-categories-page')

@section('content')

@php
    $isEdit = $category->exists;

    $isActive = old(
        'is_active',
        $isEdit ? (bool) $category->is_active : true
    );

    $currentImage = $category->gambar
        ? (
            \Illuminate\Support\Str::startsWith(
                $category->gambar,
                ['http://', 'https://']
            )
                ? $category->gambar
                : asset('storage/' . ltrim($category->gambar, '/'))
        )
        : null;
@endphp

<div class="ac-page">

    <section class="ac-intro">
        <div>
            <p class="ac-eyebrow">ADMIN CONSOLE / KATALOG</p>

            <h1>
                {{ $isEdit ? 'Edit' : 'Tambah' }}
                <span>kategori.</span>
            </h1>

            <p class="ac-description">
                Atur informasi kategori agar produk lokal
                tersusun dengan rapi.
            </p>
        </div>

        <a href="{{ route('admin.categories.index') }}"
           class="ac-back-button">
            ← Kembali ke kategori
        </a>
    </section>


    @if ($errors->any())
        <div class="ac-alert ac-alert-error" role="alert">
            <strong>Periksa kembali data kategori.</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <form
        action="{{ $isEdit
            ? route('admin.categories.update', $category)
            : route('admin.categories.store') }}"
        method="POST"
        enctype="multipart/form-data"
        class="ac-form"
    >
        @csrf

        @if ($isEdit)
            @method('PUT')
        @endif


        <section class="ac-form-panel">

            <div class="ac-form-heading">
                <span class="ac-form-heading-icon">▤</span>

                <div>
                    <h2>Informasi kategori</h2>
                    <p>Isi identitas kategori produk.</p>
                </div>
            </div>


            <div class="ac-field">

                <label for="nama">
                    Nama kategori <span>*</span>
                </label>

                <input
                    type="text"
                    id="nama"
                    name="nama"
                    value="{{ old('nama', $category->nama) }}"
                    placeholder="Contoh: Makanan & Minuman"
                    maxlength="150"
                    required
                    autofocus
                >

                <small>
                    Slug URL dibuat otomatis berdasarkan nama kategori.
                </small>

            </div>


            <div class="ac-field">

                <label for="deskripsi">
                    Deskripsi kategori
                </label>

                <textarea
                    id="deskripsi"
                    name="deskripsi"
                    rows="5"
                    placeholder="Jelaskan jenis produk yang termasuk dalam kategori ini..."
                >{{ old('deskripsi', $category->deskripsi) }}</textarea>

                <small>
                    Deskripsi bersifat opsional, maksimal 5.000 karakter.
                </small>

            </div>


            <div class="ac-field">

                <label for="gambar">
                    Gambar kategori
                </label>

                <input
                    type="file"
                    id="gambar"
                    name="gambar"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                >

                <small>
                    Format JPG, PNG, atau WEBP. Maksimal 2 MB.
                </small>

                @if ($currentImage)
                    <div class="ac-current-image">
                        <img
                            src="{{ $currentImage }}"
                            alt="Gambar kategori {{ $category->nama }}"
                        >

                        <div>
                            <strong>Gambar saat ini</strong>
                            <span>
                                Pilih gambar baru untuk menggantinya.
                            </span>
                        </div>
                    </div>
                @endif

            </div>


            <div class="ac-active-field">

                <div>
                    <strong>Aktifkan kategori</strong>

                    <p>
                        Kategori dapat dipilih saat mengelola produk
                        apabila statusnya aktif.
                    </p>
                </div>

                <label class="ac-switch">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked((bool) $isActive)
                    >

                    <span class="ac-switch-track"></span>
                </label>

            </div>

        </section>


        <div class="ac-form-actions">

            <a
                href="{{ route('admin.categories.index') }}"
                class="ac-cancel-button"
            >
                Batal
            </a>

            <button type="submit" class="ac-primary-button">
                <span>✓</span>

                {{ $isEdit ? 'Simpan perubahan' : 'Simpan kategori' }}
            </button>

        </div>

    </form>

</div>

@endsection
