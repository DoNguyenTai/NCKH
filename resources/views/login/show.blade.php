<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin sinh viên</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Thông tin Sinh viên</h2>

        @if(!empty($data))
            <table class="table table-bordered">
                <tr>
                    <th>Mã số sinh viên</th>
                    <td>{{ $data['mssv'] ?? 'Không có dữ liệu' }}</td>
                </tr>
                <tr>
                    <th>Họ tên</th>
                    <td>{{ $data['data']['fullname'] ?? 'Không có dữ liệu' }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $data['email'] ?? 'Không có dữ liệu' }}</td>
                </tr>
                <tr>
                    <th>Ngành học</th>
                    <td>{{ $data['major'] ?? 'Không có dữ liệu' }}</td>
                </tr>
                <tr>
                    <th>Năm học</th>
                    <td>{{ $data['year'] ?? 'Không có dữ liệu' }}</td>
                </tr>
            </table>
        @else
            <div class="alert alert-danger">
                Không có dữ liệu sinh viên.
            </div>
        @endif

        <a href="{{ url('/') }}" class="btn btn-primary mt-3">Quay lại</a>
    </div>
</body>
</html>
