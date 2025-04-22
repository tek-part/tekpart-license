@extends('layouts.app')

@section('title', 'حالة الترخيص')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">حالة الترخيص</h5>
                </div>
                <div class="card-body">
                    @if (session('message'))
                        <div class="alert alert-{{ session('status') }}">
                            {{ session('message') }}
                        </div>
                    @endif

                    <div class="text-center mb-4">
                        @if ($isValid)
                            <div class="mb-3">
                                <i class="fas fa-check-circle text-success fa-5x"></i>
                            </div>
                            <h4 class="text-success">الترخيص صالح</h4>
                        @else
                            <div class="mb-3">
                                <i class="fas fa-times-circle text-danger fa-5x"></i>
                            </div>
                            <h4 class="text-danger">الترخيص غير صالح أو منتهي الصلاحية</h4>
                            <p>
                                <a href="{{ route('license.activate.form') }}" class="btn btn-primary mt-3">
                                    <i class="fas fa-key me-2"></i> تفعيل الترخيص
                                </a>
                            </p>
                        @endif
                    </div>

                    @if ($licenseData)
                        <div class="table-responsive mt-4">
                            <table class="table table-striped">
                                <tr>
                                    <th>مفتاح الترخيص:</th>
                                    <td>{{ $licenseData['key'] }}</td>
                                </tr>
                                <tr>
                                    <th>المنتج:</th>
                                    <td>{{ $licenseData['product'] }}</td>
                                </tr>
                                <tr>
                                    <th>صاحب الترخيص:</th>
                                    <td>{{ $licenseData['owner'] }}</td>
                                </tr>
                                <tr>
                                    <th>النطاق:</th>
                                    <td>{{ $licenseData['domain'] }}</td>
                                </tr>
                                <tr>
                                    <th>تاريخ الإصدار:</th>
                                    <td>{{ $licenseData['issued_at'] }}</td>
                                </tr>
                                <tr>
                                    <th>تاريخ الانتهاء:</th>
                                    <td>{{ $licenseData['expiration'] }}</td>
                                </tr>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection