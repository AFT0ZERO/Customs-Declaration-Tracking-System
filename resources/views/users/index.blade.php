@extends('master')

@section('title', 'المستخدمون')

@section('content')
    <div class="main-content">
        <div class="container mt-5">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mb-4">
                <h2 class="mb-3 mb-sm-0">إدارة المستخدمين</h2>
                <a href="{{ route('users.create') }}" class="btn btn-success">مستخدم جديد</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="alert-show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="GET" action="{{ route('users.index') }}" class="d-flex mb-3">
                <input type="text" name="search" class="form-control" placeholder="بحث عن مستخدم..." value="{{ $search }}">
                <button type="submit" class="btn btn-warning ms-2">بحث</button>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-success">
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>رقم المستخدم</th>
                            <th>مدير</th>
                            <th>العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $user->userId }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span
                                        class="badge {{ $user->is_admin ? 'bg-success' : 'bg-secondary' }}">{{ $user->is_admin ? 'نعم' : 'لا' }}</span>
                                </td>
                                <td class="d-flex gap-2">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-success">تعديل</a>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST"
                                        onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">لا يوجد مستخدمون</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $users->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection