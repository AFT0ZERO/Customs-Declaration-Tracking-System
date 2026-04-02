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
                        @if(auth()->user()->is_admin)
                            <form action="{{ route('declaration.massForceDelete') }}" method="POST" id="massForceDeleteForm" style="display: none;">
                                @csrf
                                <div id="hiddenForceDeleteDeclarationIds"></div>
                                <button type="submit" class="btn btn-danger" id="massForceDeleteBtn">
                                    <i class="bi bi-trash"></i> حذف نهائي
                                </button>
                            </form>
                        @endif
                    </div>
                    @include('partials.search-tags', ['action' => route('declaration.showRestore'), 'searchValue' => request('search')])
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
                            <x-sortable-column label="رقم البيان" sort-key="declaration_number" />
                            <x-sortable-column label="مركز البيان" sort-key="declaration_type" />
                            <x-sortable-column label="السنة" sort-key="year" />
                            <x-sortable-column label="الحالة الحالية" sort-key="status" />
                            <x-sortable-column label="تاريخ الإضافة" sort-key="created_at" />
                            <x-sortable-column label="اخر تعديل" sort-key="updated_at" />
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
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('declaration.showHistory', $declaration->id) }}"
                                            class="btn btn-warning text-white" title="عرض حركات البيان">
                                            <i class="bi bi-clock"></i>
                                        </a>

                                        <button type="button" class="btn btn-primary text-white edit-statement-btn"
                                            data-id="{{ $declaration->id }}"
                                            data-number="{{ $declaration->declaration_number }}"
                                            data-status="{{ $declaration->status }}"
                                            data-year="{{ $declaration->year }}"
                                            data-type="{{ $declaration->declaration_type }}"
                                            title="تحرير البيان">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Edit Statement Modal -->
            <div class="modal fade" id="editStatementModal" tabindex="-1" aria-labelledby="editStatementModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editStatementModalLabel">تحرير البيان</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-sm">
                                <tr>
                                    <th>رقم البيان:</th>
                                    <td id="modalDeclarationNumber"></td>
                                </tr>
                                <tr>
                                    <th>المركز:</th>
                                    <td id="modalDeclarationType"></td>
                                </tr>
                                <tr>
                                    <th>السنة:</th>
                                    <td id="modalDeclarationYear"></td>
                                </tr>
                                <tr>
                                    <th>الحالة:</th>
                                    <td id="modalDeclarationStatus"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="modal-footer d-flex justify-content-between">
                            <div class="d-flex gap-2">
                                <a id="modalRestoreBtn" href="#" class="btn btn-success">
                                    <i class="bi bi-arrow-counterclockwise"></i> استرجاع البيان
                                </a>

                                @if(auth()->user()->is_admin)
                                    <form id="modalForceDeleteForm" method="POST" action="">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger"
                                            onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا البيان نهائياً؟ هذا الإجراء غير قابل للتراجع.')">
                                            <i class="bi bi-trash"></i> حذف نهائي
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        </div>
                    </div>
                </div>
            </div>
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
            const massForceDeleteForm = document.getElementById('massForceDeleteForm');
            const hiddenForceDeleteDeclarationIdsContainer = document.getElementById('hiddenForceDeleteDeclarationIds');

            function updateMassButtonsVisibility() {
                const checkedCount = document.querySelectorAll('.restore-row-checkbox:checked').length;
                if (checkedCount > 0) {
                    if (massRestoreForm) massRestoreForm.style.display = 'inline-block';
                    if (massForceDeleteForm) massForceDeleteForm.style.display = 'inline-block';
                } else {
                    if (massRestoreForm) massRestoreForm.style.display = 'none';
                    if (massForceDeleteForm) massForceDeleteForm.style.display = 'none';
                }
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function () {
                    rowCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateMassButtonsVisibility();
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
                    
                    updateMassButtonsVisibility();
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

            if (massForceDeleteForm) {
                massForceDeleteForm.addEventListener('submit', function (e) {
                    hiddenForceDeleteDeclarationIdsContainer.innerHTML = ''; // Clear previous
                    const checkedCheckboxes = document.querySelectorAll('.restore-row-checkbox:checked');
                    
                    if (checkedCheckboxes.length === 0) {
                        e.preventDefault();
                        alert('يرجى تحديد بيان واحد على الأقل.');
                        return;
                    }

                    if (!confirm('هل أنت متأكد أنك تريد حذف البيانات المحددة نهائياً؟ هذا الإجراء غير قابل للتراجع.')) {
                        e.preventDefault();
                        return;
                    }

                    checkedCheckboxes.forEach(checkbox => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'declaration_ids[]';
                        input.value = checkbox.value;
                        hiddenForceDeleteDeclarationIdsContainer.appendChild(input);
                    });
                });
            }

            // Modal logic
            const editStatementButtons = document.querySelectorAll('.edit-statement-btn');
            const editStatementModal = new bootstrap.Modal(document.getElementById('editStatementModal'));
            const modalDeclarationNumber = document.getElementById('modalDeclarationNumber');
            const modalDeclarationType = document.getElementById('modalDeclarationType');
            const modalDeclarationYear = document.getElementById('modalDeclarationYear');
            const modalDeclarationStatus = document.getElementById('modalDeclarationStatus');
            const modalRestoreBtn = document.getElementById('modalRestoreBtn');
            const modalForceDeleteForm = document.getElementById('modalForceDeleteForm');

            editStatementButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.dataset.id;
                    modalDeclarationNumber.textContent = this.dataset.number;
                    modalDeclarationType.textContent = this.dataset.type;
                    modalDeclarationYear.textContent = this.dataset.year;
                    modalDeclarationStatus.textContent = this.dataset.status;

                    modalRestoreBtn.href = `/dashboard/restore/${id}`;
                    if (modalForceDeleteForm) {
                        modalForceDeleteForm.action = `/declaration/force-delete/${id}`;
                    }

                    editStatementModal.show();
                });
            });
        });
    </script>
@endsection