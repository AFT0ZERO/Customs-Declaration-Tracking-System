@extends('master')

@section('title', 'الاحصائيات')

@section('content')
    <div class="main-content">
        <div class="container mt-5">
            <!-- Header Section -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="text-center text-sm-start mb-4"> إحصائيات البيانات الجمركية</h1>
                </div>
                <a href="{{ route('dashboard') }}" style="color: white; text-decoration: none;">
                    <button class="btn btn-success mt-3 mt-sm-0">العودة للرئيسية</button>
                </a>
            </div>

            <div class="row">
                <!-- Data per Year -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>عدد البيانات حسب السنة</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th>السنة</th>
                                            <th>عدد البيانات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($years as $yearData)
                                            <tr>
                                                <td><span class="badge bg-secondary fs-6">{{ $yearData->year }}</span></td>
                                                <td><span class="badge bg-primary fs-6">{{ $yearData->count }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-muted">لا يوجد بيانات</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data per Status -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bi bi-bar-chart-fill me-2"></i>عدد البيانات حسب الحالة</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th>الحالة</th>
                                            <th>عدد البيانات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($statuses as $statusData)
                                            <tr>
                                                <td>{{ $statusData->status }}</td>
                                                <td><span class="badge bg-primary fs-6">{{ $statusData->count }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-muted">لا يوجد بيانات</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
