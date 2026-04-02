@extends('master')

@section('title', 'الصفحة الرئيسية')

@section('content')
    <div class="main-content">
        <div class="container mt-5">
            <!-- Header Section -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-center text-sm-start mb-4">اسم المستخدم :{{Auth::user()->name}} <br> حركة البيانات
                        الجمركية </h2>
                    <div class="d-flex flex-wrap gap-2 mb-3 mt-3 mt-sm-0">
                        <button class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#addDeclarationModal">إضافة بيان جديد
                        </button>
                        <button class="btn btn-warning text-dark" id="massEditBtn" data-bs-toggle="modal"
                            data-bs-target="#massEditModal" style="display: none;">
                            <i class="bi bi-pencil-square"></i> تعديل المحدد
                        </button>
                    </div>
                    @include('partials.search-tags', ['action' => route('dashboard'), 'searchValue' => request('search')])
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
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>#</th>
                            <x-sortable-column label="رقم البيان" sort-key="declaration_number" />
                            <x-sortable-column label="مركز البيان" sort-key="declaration_type" />
                            <x-sortable-column label="سنة" sort-key="year" />
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
                                    <input type="checkbox" class="form-check-input row-checkbox" value="{{ $declaration->id }}">
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
                                            class="btn btn-warning text-white" title="عرض حركات البيان">
                                            <i class="bi bi-clock"></i>
                                        </a>
                                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#editStatusModal"
                                            data-id="{{ $declaration->id }}" data-status="{{ $declaration->status }}"
                                            data-number="{{ $declaration->declaration_number }}"
                                            data-type="{{ $declaration->declaration_type }}" data-year="{{ $declaration->year }}"
                                            data-description="{{ $declaration->description }}" title="تعديل على البيان ">
                                            <i class="bi bi-pencil"></i>
                                        </button>
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

            <!-- Add Declaration Modal -->
            <div class="modal fade" id="addDeclarationModal" tabindex="-1" role="dialog"
                aria-labelledby="addDeclarationModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addDeclarationModalLabel">إضافة بيان جديد</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('declaration.store') }}" method="POST">
                                @csrf
                                <div class="form-group mb-3">
                                    <label for="declaration_number">رقم البيان *</label>
                                    <input type="text" id="declaration_number" name="declaration_number"
                                        class="form-control">

                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="declaration_type">مركز البيان </label>
                                            <select name="declaration_type" id="declaration_type"
                                                class="form-control custom-select" required>
                                                <option value="220">220</option>
                                                <option value="224">224</option>
                                                <option value="900">900</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="year">السنة</label>
                                            <select name="year" id="year" class="form-control custom-select" required>
                                                <option value="2026">2026</option>
                                                <option value="2025">2025</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="status"> الحالة</label>
                                    <select name="status" id="status" class="form-control custom-select " required>
                                        <option value="عمان لغايات الفحص">عمان لغايات الفحص</option>
                                        <option value="العقبة ساحة 4 غذاء">العقبة ساحة 4 غذاء</option>
                                        <option value="العقبة غذاء البلد">العقبة غذاء البلد</option>
                                        <option value="العقبة مكتب 4">العقبة مكتب 4</option>
                                        <option value="العقبة الارشيف">العقبة الارشيف</option>
                                        <option value="عمان التاجر">عمان التاجر</option>
                                        <option value="عمان">عمان</option>
                                    </select>

                                </div>
                                <div class="form-group mb-3">
                                    <label for="description">وصف اضافي </label>
                                    <textarea id="description" name="description" class="form-control"></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">إضافة البيان</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Edit Status Modal -->
            <div class="modal fade" id="editStatusModal" tabindex="-1" role="dialog" aria-labelledby="editStatusModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editStatusModalLabel">تعديل الحالة</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('declaration.updateStatus', ':id') }}" method="POST"
                                id="updateStatusForm">
                                @csrf
                                @method('PUT')
                                <div class="form-group mb-3">
                                    <label for="edit-declaration-number">رقم البيان </label>
                                    <input type="text" name="editNumber" value="" id="edit-declaration-number"
                                        class="form-control">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="edit-declaration-type">مركز البيان </label>
                                            <select name="declaration_type" id="edit-declaration-type"
                                                class="form-control custom-select" required>
                                                <option value="220">220</option>
                                                <option value="224">224</option>
                                                <option value="900">900</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="edit-year">السنة</label>
                                            <select name="year" id="edit-year" class="form-control custom-select" required>
                                                <option value="2026">2026</option>
                                                <option value="2025">2025</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-3 ">
                                    <label for="edit-status">الحالة</label>
                                    <select name="status" id="edit-status" class="form-control custom-select" required>
                                        <option value="عمان لغايات الفحص">عمان لغايات الفحص</option>
                                        <option value="العقبة ساحة 4 غذاء">العقبة ساحة 4 غذاء</option>
                                        <option value="العقبة غذاء البلد">العقبة غذاء البلد</option>
                                        <option value="العقبة مكتب 4">العقبة مكتب 4</option>
                                        <option value="العقبة الارشيف">العقبة الارشيف</option>
                                        <option value="عمان التاجر">عمان التاجر</option>
                                        <option value="عمان">عمان</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="edit-description">وصف إضافي</label>
                                    <textarea name="editDescription" id="edit-description" class="form-control"></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">تعديل الحالة</button>
                            </form>
                        </div>

                    </div>
                    </div>
                </div>
            </div>

            <!-- Mass Edit Status Modal -->
            <div class="modal fade" id="massEditModal" tabindex="-1" aria-labelledby="massEditModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="massEditModalLabel">تعديل حالة البيانات المحددة</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('declaration.massUpdateStatus') }}" method="POST" id="massUpdateStatusForm">
                            <div class="modal-body">
                                @csrf
                                <div id="hiddenDeclarationIds"></div>
                                <div class="alert alert-info">
                                    سيتم تعديل الحالة لـ <span id="selectedCountDisplay" class="fw-bold fs-5">0</span> بيان.
                                </div>
                                <div class="form-group mb-3 ">
                                    <label for="mass-edit-status">الحالة</label>
                                    <select name="status" id="mass-edit-status" class="form-control custom-select" required>
                                        <option value="عمان لغايات الفحص">عمان لغايات الفحص</option>
                                        <option value="العقبة ساحة 4 غذاء">العقبة ساحة 4 غذاء</option>
                                        <option value="العقبة غذاء البلد">العقبة غذاء البلد</option>
                                        <option value="العقبة مكتب 4">العقبة مكتب 4</option>
                                        <option value="العقبة الارشيف">العقبة الارشيف</option>
                                        <option value="عمان التاجر">عمان التاجر</option>
                                        <option value="عمان">عمان</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="mass-edit-description">وصف إضافي</label>
                                    <textarea name="description" id="mass-edit-description" class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                <button type="submit" class="btn btn-success">تأكيد التعديل</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('addDeclarationModal');

            modal.addEventListener('shown.bs.modal', function () {
                document.getElementById('declaration_number').focus();
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Close success alert after 3 seconds
            setTimeout(function () {
                $('#alert-show').fadeOut('slow', function () {
                    $(this).remove();
                });
            }, 3000);

            // Set data to Edit Modal
            $('#editStatusModal').on('shown.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var declarationId = button.data('id');
                var declarationStatus = button.data('status');
                var declarationNumber = button.data('number');
                var declarationType = button.data('type');
                var declarationYear = button.data('year');
                var declarationDescription = button.data('description');
                var actionUrl = "{{ route('declaration.updateStatus', ':id') }}";
                actionUrl = actionUrl.replace(':id', declarationId);

                // Convert to string to ensure proper matching with select option values
                declarationType = String(declarationType || '').trim();
                declarationYear = String(declarationYear || '').trim();

                // Set form action and field values
                $('#updateStatusForm').attr('action', actionUrl);
                $('#edit-status').val(declarationStatus);
                $('#edit-declaration-number').val(declarationNumber);

                // Set select values - ensure they match option values exactly
                // Use setTimeout to ensure DOM is ready
                setTimeout(function () {
                    $('#edit-declaration-type').val(declarationType);
                    $('#edit-year').val(declarationYear);
                }, 10);

                // Set description if exists
                if (declarationDescription) {
                    $('#edit-description').val(declarationDescription);
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('#addDeclarationModal form');
            const focusableElements = form.querySelectorAll('input, select, textarea, button');

            focusableElements.forEach((element, index) => {
                element.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' && !event.shiftKey) {
                        event.preventDefault();
                        const nextIndex = index + 1;

                        if (nextIndex < focusableElements.length) {
                            focusableElements[nextIndex].focus();
                        } else {
                            // Submit form when reaching last element
                            form.submit();
                        }
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const selectAllCheckbox = document.getElementById('selectAll');
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');
            const massEditBtn = document.getElementById('massEditBtn');
            const massUpdateStatusForm = document.getElementById('massUpdateStatusForm');
            const hiddenDeclarationIdsContainer = document.getElementById('hiddenDeclarationIds');
            const selectedCountDisplay = document.getElementById('selectedCountDisplay');

            function updateMassEditButtonVisibility() {
                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                if (checkedCount > 0) {
                    massEditBtn.style.display = 'inline-block';
                } else {
                    massEditBtn.style.display = 'none';
                }
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function () {
                    rowCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateMassEditButtonVisibility();
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
                    
                    updateMassEditButtonVisibility();
                });
            });

            if (massUpdateStatusForm) {
                massUpdateStatusForm.addEventListener('submit', function (e) {
                    hiddenDeclarationIdsContainer.innerHTML = ''; // Clear previous
                    const checkedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
                    
                    if (checkedCheckboxes.length === 0) {
                        e.preventDefault();
                        alert('يرجى تحديد بيان واحد على الأقل.');
                        return;
                    }

                    checkedCheckboxes.forEach(checkbox => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'declaration_ids[]';
                        input.value = checkbox.value;
                        hiddenDeclarationIdsContainer.appendChild(input);
                    });
                });
            }

            // Update count before modal shows up
            const massEditModal = document.getElementById('massEditModal');
            if (massEditModal) {
                massEditModal.addEventListener('show.bs.modal', function () {
                    const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                    selectedCountDisplay.textContent = checkedCount;
                });
            }
        });

    </script>

@endsection