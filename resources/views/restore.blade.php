@extends('master')

@section('title', 'صفحة الارشيف')

@section('content')
    <div class="main-content">
        <div class="container mt-5">
            <!-- Header Section -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="text-center text-sm-start mb-4"> ارشيف البيانات الجمركية</h1>
                    <div class="d-flex flex-wrap gap-2 mb-3 mt-3 mt-sm-0">
                        <form action="{{ route('declaration.massRestore') }}" method="POST" id="massRestoreForm" style="display: none;">
                            @csrf
                            <div id="hiddenRestoreDeclarationIds"></div>
                            <button type="submit" class="btn btn-success" id="massRestoreBtn">
                                <i class="bi bi-arrow-counterclockwise"></i> استرجاع المحدد
                            </button>
                        </form>
                    </div>
                    <form method="GET" action="{{route('declaration.showRestore')}}" class="d-flex mt-3 mt-sm-0 gap-2">
                        <input type="text" name="search" class="form-control" placeholder="بحث عن بيان..."
                            aria-label="Search..." value="{{ request('search') }}">
                        <input type="hidden" name="sort" value="{{ request('sort', 'created_at') }}">
                        <input type="hidden" name="direction" value="{{ request('direction', 'desc') }}">
                        <button type="submit" class="btn btn-warning">بحث</button>
                    </form>
                </div>
                <div class="d-flex flex-column align-items-end">
                    <a href="{{route("dashboard")}}" style="color: white; text-decoration:none">
                        <button class="btn btn-success mt-3 mt-sm-0"> العوده</button>
                    </a>
                </div>
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
                            <th>
                                <input type="checkbox" id="selectAllRestore" class="form-check-input">
                            </th>
                            <th>#</th>
                            <th>
                               رقم البيان
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'declaration_number', 'direction' => 'asc']) }}"
                                        class="btn btn-sm {{ request('sort') === 'declaration_number' && request('direction') === 'asc' ? 'btn-success' : '' }}">
                                        <i class="bi bi-arrow-up"></i>
                                    </a>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'declaration_number', 'direction' => 'desc']) }}"
                                        class="btn btn-sm {{ request('sort') === 'declaration_number' && request('direction') === 'desc' ? 'btn-success' : '' }}">
                                        <i class="bi bi-arrow-down"></i>
                                    </a>
                                </div>
                            </th>
                            <th>
                                مركز البيان
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'declaration_type', 'direction' => 'asc']) }}"
                                        class="btn btn-sm {{ request('sort') === 'declaration_type' && request('direction') === 'asc' ? 'btn-success' : '' }}">
                                        <i class="bi bi-arrow-up"></i>
                                    </a>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'declaration_type', 'direction' => 'desc']) }}"
                                        class="btn btn-sm {{ request('sort') === 'declaration_type' && request('direction') === 'desc' ? 'btn-success' : '' }}">
                                        <i class="bi bi-arrow-down"></i>
                                    </a>
                                </div>
                            </th>
                            <th>
                                السنة
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'year', 'direction' => 'asc']) }}"
                                        class="btn btn-sm {{ request('sort') === 'year' && request('direction') === 'asc' ? 'btn-success' : '' }}">
                                        <i class="bi bi-arrow-up"></i>
                                    </a>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'year', 'direction' => 'desc']) }}"
                                        class="btn btn-sm {{ request('sort') === 'year' && request('direction') === 'desc' ? 'btn-success' : '' }}">
                                        <i class="bi bi-arrow-down"></i>
                                    </a>
                                </div>
                            </th>
                            <th>
                                الحالة الحالية
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => 'asc']) }}"
                                        class="btn btn-sm {{ request('sort') === 'status' && request('direction') === 'asc' ? 'btn-success' : '' }}">
                                        <i class="bi bi-arrow-up"></i>
                                    </a>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => 'desc']) }}"
                                        class="btn btn-sm {{ request('sort') === 'status' && request('direction') === 'desc' ? 'btn-success' : '' }}">
                                        <i class="bi bi-arrow-down"></i>
                                    </a>
                                </div>
                            </th>
                            <th>
                                تاريخ الإضافة
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => 'asc']) }}"
                                        class="btn btn-sm {{ request('sort') === 'created_at' && request('direction') === 'asc' ? 'btn-success' : '' }}">
                                        <i class="bi bi-arrow-up"></i>
                                    </a>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => 'desc']) }}"
                                        class="btn btn-sm {{ request('sort') === 'created_at' && request('direction') === 'desc' ? 'btn-success' : '' }}">
                                        <i class="bi bi-arrow-down"></i>
                                    </a>
                                </div>
                            </th>
                            <th>
                                اخر تعديل
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'updated_at', 'direction' => 'asc']) }}"
                                        class="btn btn-sm {{ request('sort') === 'updated_at' && request('direction') === 'asc' ? 'btn-success' : '' }}">
                                        <i class="bi bi-arrow-up"></i>
                                    </a>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'updated_at', 'direction' => 'desc']) }}"
                                        class="btn btn-sm {{ request('sort') === 'updated_at' && request('direction') === 'desc' ? 'btn-success' : '' }}">
                                        <i class="bi bi-arrow-down"></i>
                                    </a>
                                </div>
                            </th>
                            <th>العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($declarations->items() == [])
                            <div class="alert alert-danger alert-dismissible fade show" id="alert-show">
                                لا يوجد بيانات مطابقة
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @foreach($declarations as $declaration)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input restore-row-checkbox" value="{{ $declaration->id }}">
                                </td>
                                <td>{{$loop->iteration}}</td>
                                <td>{{ $declaration->declaration_number }}</td>
                                <td>{{ $declaration->declaration_type }}</td>
                                <td>{{ $declaration->year }}</td>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Close success alert after 3 seconds
            setTimeout(function () {
                $('#alert-show').fadeOut('slow', function () {
                    $(this).remove();
                });
            }, 3000);
        });

        document.addEventListener('DOMContentLoaded', function () {
            const selectAllCheckbox = document.getElementById('selectAllRestore');
            const rowCheckboxes = document.querySelectorAll('.restore-row-checkbox');
            const massRestoreForm = document.getElementById('massRestoreForm');
            const hiddenRestoreDeclarationIdsContainer = document.getElementById('hiddenRestoreDeclarationIds');

            function updateMassRestoreButtonVisibility() {
                const checkedCount = document.querySelectorAll('.restore-row-checkbox:checked').length;
                if (checkedCount > 0) {
                    massRestoreForm.style.display = 'inline-block';
                } else {
                    massRestoreForm.style.display = 'none';
                }
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function () {
                    rowCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateMassRestoreButtonVisibility();
                });
            }

            rowCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    const allChecked = Array.from(rowCheckboxes).every(c => c.checked);
                    const someChecked = Array.from(rowCheckboxes).some(c => c.checked);
                    
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = allChecked;
                        selectAllCheckbox.indeterminate = someChecked && !allChecked;
                    }
                    
                    updateMassRestoreButtonVisibility();
                });
            });

            if (massRestoreForm) {
                massRestoreForm.addEventListener('submit', function (e) {
                    hiddenRestoreDeclarationIdsContainer.innerHTML = ''; // Clear previous
                    const checkedCheckboxes = document.querySelectorAll('.restore-row-checkbox:checked');
                    
                    if (checkedCheckboxes.length === 0) {
                        e.preventDefault();
                        alert('يرجى تحديد بيان واحد على الأقل.');
                        return;
                    }

                    if (!confirm('هل أنت متأكد أنك تريد استرجاع البيانات المحددة؟')) {
                        e.preventDefault();
                        return;
                    }

                    checkedCheckboxes.forEach(checkbox => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'declaration_ids[]';
                        input.value = checkbox.value;
                        hiddenRestoreDeclarationIdsContainer.appendChild(input);
                    });
                });
            }
        });
    </script>

@endsection