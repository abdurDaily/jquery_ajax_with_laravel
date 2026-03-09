<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    @vite('resources/js/app.js')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container my-3 p-5">
        <h1>Welcome to Laravel</h1>

        <div class="row mt-5 gap-3 justify-content-between">
            <div class="col-xl-5 shadow-sm p-0">
                <div class="p-3">
                    <form action="{{ route('store') }}" method="POST">
                        @csrf
                        <input type="hidden" id="user_id" name="user_id">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <button type="submit" class="btn btn-primary" id="userForm">Submit</button>
                    </form>
                </div>
            </div>
            <div class="col-xl-6 shadow-sm p-0 h-100">
                <div class="p-3">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tbody">
                            @forelse ($users as $user)
                                <tr data-id="{{ $user->id }}">
                                    <td class="name">{{ $user->name }}</td>
                                    <td class="email">{{ $user->email }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning editBtn"
                                            data-id="{{ $user->id }}">Edit</button>
                                        <button class="btn btn-sm btn-danger deleteBtn"
                                            data-id="{{ $user->id }}">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="nodata">
                                    <td colspan="3" class="text-center">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn-script.com/ajax/libs/jquery/3.7.1/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {

            let form = $('form');

            // ── STORE / UPDATE ──────────────────────────────────
            form.on('submit', function(e) {
                e.preventDefault();
                let name = $('#name').val();
                let email = $('#email').val();
                let password = $('#password').val();
                let userId = $('#user_id').val();

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: {
                        name: name,
                        email: email,
                        password: password,
                        user_id: userId,
                        _token: "{{ csrf_token() }}",
                        _method: userId ? 'PUT' : undefined  // method spoofing for update
                    },
                    success: function(response) {
                        let user = response.data;

                        if (userId) {
                            // Update existing row in place
                            let row = $('tr[data-id="' + user.id + '"]');
                            row.find('.name').text(user.name);
                            row.find('.email').text(user.email);
                        } else {
                            // Prepend new row
                            $('#nodata').remove();
                            let newRow = `
                                <tr data-id="${user.id}">
                                    <td class="name">${user.name}</td>
                                    <td class="email">${user.email}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning editBtn" data-id="${user.id}">Edit</button>
                                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${user.id}">Delete</button>
                                    </td>
                                </tr>
                            `;
                            $('#tbody').prepend(newRow);
                        }

                        const Toast = Swal.mixin({
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener("mouseenter", Swal.stopTimer);
                                toast.addEventListener("mouseleave", Swal.resumeTimer);
                            }
                        });

                        Toast.fire({
                            icon: "success",
                            title: response.message
                        });

                        // Reset form
                        $('#name').val('');
                        $('#email').val('');
                        $('#password').val('');
                        $('#user_id').val('');
                        $('button[type="submit"]').text('Submit');
                        form.attr('action', "{{ route('store') }}");
                    },
                    error: function(errres) {
                        console.log(errres.responseJSON.message);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errres.responseJSON.message
                        });
                    }
                });
            });

            // ── EDIT (populate form) ────────────────────────────
            $(document).on('click', '.editBtn', function() {
                let row = $(this).closest('tr');
                let userId = $(this).data('id');

                let name = row.find('.name').text();
                let email = row.find('.email').text();

                $('#name').val(name);
                $('#email').val(email);
                $('#password').val('');
                $('#user_id').val(userId);

                form.attr('action', "{{ route('update', ':id') }}".replace(':id', userId));
                $('button[type="submit"]').text('Update');
            });

            // ── DELETE ──────────────────────────────────────────
            $(document).on('click', '.deleteBtn', function() {
                let userId = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This user will be permanently deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: "{{ route('destroy', ':id') }}".replace(':id', userId),
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            $('tr[data-id="' + userId + '"]').fadeOut(300, function() {
                                $(this).remove();
                                if ($('#tbody tr').length === 0) {
                                    $('#tbody').append(`
                                        <tr id="nodata">
                                            <td colspan="3" class="text-center">No users found.</td>
                                        </tr>
                                    `);
                                }
                            });

                            const Toast = Swal.mixin({
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                didOpen: (toast) => {
                                    toast.addEventListener("mouseenter", Swal.stopTimer);
                                    toast.addEventListener("mouseleave", Swal.resumeTimer);
                                }
                            });

                            Toast.fire({
                                icon: "success",
                                title: response.message
                            });
                        },
                        error: function(errres) {
                            console.log(errres.responseJSON.message);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errres.responseJSON.message
                            });
                        }
                    });
                });
            });

        });
    </script>
</body>

</html>