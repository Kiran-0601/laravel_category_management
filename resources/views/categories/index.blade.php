@extends('layouts.app')

@section('title', 'Manage Categories')
@section('content')
<div class="d-flex align-items-center mb-3">
    <h2>Category List</h2>
    <div class="ms-auto">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">Add Category</button>
    </div>
</div>
<br>
<table id="categoryTable" class="table table-bordered">
    <thead>
        <tr>
            <th>Parent category</th>
            <th>Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm">
                    <div class="row">
                        <!-- Parent Category Dropdown -->
                        <div class="col-md-12">
                            <label class="form-label">Parent Category</label>
                            <select class="form-control" id="parent_id" name="parent_id">
                                <option value="">Select Parent Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="name" name="name">
                                <div class="text-danger" id="nameError"></div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('page-script')
<script type="text/javascript">
$(document).ready(function () {
    var table = $('#categoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('categories.list') }}",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: function (d) {
                d.search = $('#categoryTable_filter input').val();
            }
        },
        columns: [
            { data: 'parent_name', name: 'parent_category_name', title: 'Parent Category' },
            { data: 'name', name: 'category_name', title: 'Category Name' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });

    $('#categoryForm').submit(function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        let url = '/categories/store'; // Update to your correct API route

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                toastr.success(response.message);
                $('#categoryModal').modal('hide');
                window.location.href = "{{ route('categories') }}";
                // $('#categoryTable').DataTable().ajax.reload(null, false); // Reload table
            },
            error: function (xhr) {
                let errors = xhr.responseJSON.errors;
                $('#nameError').text(errors?.name ? errors.name[0] : '');
            }
        });
    });

    $('#categoryModal').on('hidden.bs.modal', function () {
        $('#categoryForm')[0].reset(); // Reset form fields
        $('#nameError').text(''); // Clear validation error messages
    });
});
</script>
@endsection
