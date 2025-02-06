@extends('master')

@section('title', 'الصفحة الرئيسية')

@section('content')
<div class="main-content">
    <div class="container mt-5">
        <!-- Header Section -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mb-4">
            <div>
            <h2 class="text-center text-sm-start mb-4">اسم المستخدم :{{Auth::user()->name}} <br> حركة البيانات الجمركية </h2>

            <form method="GET" action="{{route('dashboard')}}" class="d-flex mt-3 mt-sm-0">
                <input
                    type="text"
                    name="search"
                    class="form-control me-2"
                    placeholder="بحث عن بيان..."
                    aria-label="Search..."
                >
                <button type="submit" class="btn btn-warning">بحث</button>
            </form>
                <a href="{{route("declaration.showRestore")}}"style="color: white ;text-decoration:none ">
                    <button class="btn btn-danger mt-3 ">الارشيف</button>
                </a>
            </div>
            <button class="btn btn-success mt-3 mt-sm-0" data-bs-toggle="modal" data-bs-target="#addDeclarationModal">إضافة بيان جديد</button>
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
                 @if($declarations->items() == [])
                        <div class="alert alert-danger alert-dismissible fade show" id="alert-show">
                            لا يوجد بيانات مطابقة
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @foreach($declarations as $declaration)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{ $declaration->declaration_number }}</td>
                        <td>{{ $declaration->status }}</td>
                        <td>{{ $declaration->created_at->format('d/m/Y')}}</td>
                        <td>{{ $declaration->updated_at->format('d/m/Y')}}</td>
                        <td>
                            <a href="{{ route('declaration.showHistory', $declaration->id) }}" class="btn btn-warning text-white" title="عرض حركات البيان">
                                <i class="bi bi-clock"></i>
                            </a>
                            <button class="btn btn-success"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editStatusModal"
                                    data-id="{{ $declaration->id }}"
                                    data-status="{{ $declaration->status }}"
                                    data-number="{{ $declaration->declaration_number }}"
                                    data-description="{{ $declaration->description }}"
                                    title="تعديل على البيان ">
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
        <div class="modal fade" id="addDeclarationModal" tabindex="-1" role="dialog" aria-labelledby="addDeclarationModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addDeclarationModalLabel">إضافة بيان جمركي جديد</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('declaration.store') }}" method="POST">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="declaration_number">رقم البيان الجمركي *</label>
                                <input type="text" id="declaration_number" name="declaration_number" class="form-control"  >

                            </div>
                            <div class="form-group mb-3">
                                <label for="status">  الحالة</label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="عمان لغايات الفحص">عمان لغايات الفحص </option>
                                    <option value="عمان مراجعة زراعة">عمان مراجعة زراعة</option>
                                    <option value="عمان مراجعة مواصفات">عمان مراجعة مواصفات </option>
                                    <option value="العقبة ساحة 4 غذاء">العقبة ساحة 4 غذاء</option>
                                    <option value="العقبة غذاء البلد">العقبة غذاء البلد</option>
                                    <option value="العقبة مكتب 4">العقبة مكتب 4  </option>
                                    <option value="العقبة الارشيف">العقبة الارشيف</option>
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Status Modal -->
        <div class="modal fade" id="editStatusModal" tabindex="-1" role="dialog" aria-labelledby="editStatusModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editStatusModalLabel">تعديل الحالة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('declaration.updateStatus', ':id') }}" method="POST" id="updateStatusForm">
                            @csrf
                           @method('PUT')
                            <div class="form-group mb-3">
                                <label for="edit-declaration-number">رقم البيان الجمركي</label>
                                <input type="text" name="editNumber" value="" id="edit-declaration-number" class="form-control"  >
                            </div>
                            <div class="form-group mb-3">
                                <label for="edit-status">الحالة</label>
                                <select name="status" id="edit-status" class="form-control" required>
                                <option value="عمان لغايات الفحص">عمان لغايات الفحص </option>
                                    <option value="عمان مراجعة زراعة">عمان مراجعة زراعة</option>
                                    <option value="عمان مراجعة مواصفات">عمان مراجعة مواصفات </option>
                                    <option value="العقبة ساحة 4 غذاء">العقبة ساحة 4 غذاء</option>
                                    <option value="العقبة غذاء البلد">العقبة غذاء البلد</option>
                                    <option value="العقبة مكتب 4">العقبة مكتب 4  </option>
                                    <option value="العقبة الارشيف">العقبة الارشيف</option>
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Close success alert after 3 seconds
        setTimeout(function() {
            $('#alert-show').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 3000);

        // Set data to Edit Modal
        $('#editStatusModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var declarationId = button.data('id');
            var declarationStatus = button.data('status');
            var declarationNumber = button.data('number');
            var declarationDescription = button.data('description');
            var actionUrl = "{{ route('declaration.updateStatus', ':id') }}";
            actionUrl = actionUrl.replace(':id', declarationId);

            // Set form action and field values
            $('#updateStatusForm').attr('action', actionUrl);
            $('#edit-status').val(declarationStatus);
            $('#edit-declaration-number').val(declarationNumber);

        });
    });
</script>

@endsection
