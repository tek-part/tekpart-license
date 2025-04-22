@extends('teklicense::layouts.license')

@section('title', 'الترخيص غير صالح')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm bg-light border-danger">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle text-danger fa-5x"></i>
                    </div>
                    <h2 class="text-danger mb-3">ترخيص غير صالح</h2>
                    <p class="lead">
                        عذرًا، ترخيص البرنامج غير صالح أو منتهي الصلاحية.
                    </p>
                    <p>
                        يرجى التواصل مع المسؤول أو شراء ترخيص جديد لاستخدام هذا البرنامج.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('license.activate.form') }}" class="btn btn-primary">
                            <i class="fas fa-key me-2"></i> تفعيل الترخيص
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-white text-center">
                    <small class="text-muted">للدعم الفني: support@tekpart.com</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
