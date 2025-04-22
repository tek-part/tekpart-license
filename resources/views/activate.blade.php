@extends('teklicense::layouts.license')

@section('title', 'تفعيل الترخيص')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">تفعيل الترخيص</h5>
                </div>
                <div class="card-body">
                    @if (session('message'))
                        <div class="alert alert-{{ session('status') }}">
                            {{ session('message') }}
                        </div>
                    @endif

                    <form action="{{ route('license.activate') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="license_key" class="form-label">مفتاح الترخيص <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('license_key') is-invalid @enderror" id="license_key" name="license_key" value="{{ old('license_key') }}" required placeholder="xxxx-xxxx-xxxx-xxxx">
                            @error('license_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="name" class="form-label">الاسم <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="domain" class="form-label">الدومين <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('domain') is-invalid @enderror" id="domain" name="domain" value="{{ old('domain') }}" required placeholder="example.com">
                            @error('domain')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key me-2"></i> تفعيل الترخيص
                            </button>
                            <a href="{{ route('license.status') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i> العودة إلى حالة الترخيص
                            </a>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-white">
                    <p class="mb-0 text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        إذا لم يكن لديك ترخيص، يرجى التواصل مع المطور للحصول على ترخيص صالح.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
