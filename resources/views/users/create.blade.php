@extends('master')

@section('title', 'مستخدم جديد')

@section('content')
    <div class="main-content">
        <div class="container mt-5">
            <h2 class="mb-4">إضافة مستخدم</h2>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" id="alert-show">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('users.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label">الاسم</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">رقم المستخدم</label>
                    <input type="text" name="userId" class="form-control" value="{{ old('userId') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">كلمة المرور</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-2 form-check">
                    <input class="form-check-input  border-secondary" type="checkbox" name="is_admin" value="1" id="is_admin">
                    <label class="form-check-label" for="is_admin">منح صلاحية المدير</label>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">حفظ</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
@endsection