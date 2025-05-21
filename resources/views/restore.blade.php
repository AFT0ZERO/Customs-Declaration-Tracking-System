@extends('master')

@section('title', 'صفحة الارشيف')

@section('content')
    <div class="main-content">
        <div class="container mt-5">
            <!-- Header Section -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="text-center text-sm-start mb-4"> ارشيف البيانات الجمركية</h1>
                    <form method="GET" action="{{route('declaration.showRestore')}}" class="d-flex mt-3 mt-sm-0">
                        <input type="text" name="search" class="form-control me-2" placeholder="بحث عن بيان..."
                            aria-label="Search...">
                        <button type="submit" class="btn btn-warning">بحث</button>
                    </form>
                </div>
                <a href="{{route("dashboard")}}" style="color: white ;text-decoration:none">
                    <button class="btn btn-success mt-3 mt-sm-0"> العوده</button>
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="alert-show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" id="alert-show">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

                </div>
            @endif
            <!-- Data Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-success">
                        <tr>
                            <th>#</th>
                            <th>رقم البيان الجمركي</th>
                            <th>الحالة الحالية</th>
                            <th>تاريخ الإضافة</th>
                            <th>اخر تعديل</th>
                            <th>العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($declarations as $declaration)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{{ $declaration->declaration_number }}</td>
                                <td>{{ $declaration->status }}</td>
                                <td>{{ $declaration->created_at->format('d/m/Y')}}</td>
                                <td>{{ $declaration->updated_at->format('d/m/Y')}}</td>
                                <td>
                                    <a href="{{ route('declaration.showHistory', $declaration->id) }}"
                                        class="btn btn-warning text-white">
                                        <i class="bi bi-clock"></i>
                                    </a>
                                    <a href="{{ route('declaration.restore', $declaration->id) }}"
                                        class="btn btn-success text-white">
                                        ارجاع البيان
                                    </a>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                <div class="pagination-container">
                    {{ $declarations->links('pagination::bootstrap-4') }}
                </div>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>



@endsection