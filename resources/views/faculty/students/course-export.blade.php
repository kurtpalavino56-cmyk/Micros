<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Enrolled students - {{ $course->title }}</title>
    <style>
        body{font-family:Arial,sans-serif;color:#24314d;font-size:11px;}
        h1{color:#13176b;font-size:20px;margin:0 0 5px;}
        p{color:#667085;margin:0 0 16px;}
        table{width:100%;border-collapse:collapse;}
        th,td{border:1px solid #d9e0ec;padding:8px;text-align:left;}
        th{background:#f1f4fb;color:#344054;}
        tbody tr:nth-child(even){background:#f9fafb;}
    </style>
</head>
<body>
    <h1>Enrolled students</h1>
    <p>{{ $course->title }} · {{ $students->count() }} {{ Illuminate\Support\Str::plural('student', $students->count()) }} · Generated {{ $generatedAt->format('F j, Y g:i A') }}</p>
    <table>
        <thead>
            <tr>
                <th>Student name</th>
                <th>Student ID</th>
                <th>Progress</th>
                <th>Status</th>
                <th>Enrolled at</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
                <tr>
                    <td>{{ $student['name'] }}</td>
                    <td>{{ $student['student_id'] }}</td>
                    <td>{{ $student['progress'] }}%</td>
                    <td>{{ $student['status'] }}</td>
                    <td>{{ $student['enrolled_at'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No students are enrolled in this course yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
