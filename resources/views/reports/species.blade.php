<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NVSU Species Directory Report</title>
    <link rel="stylesheet" href="{{ public_path('css/reports.css') }}">
</head>
<body>
    <header class="report-header">
        <h1>NVSU Species Directory Report</h1>
        <p>Generated {{ $generatedAt->format('F j, Y g:i A') }} · Current filtered records</p>
    </header>

    <p class="report-summary"><strong>{{ $species->count() }}</strong> species records included.</p>

    <table>
        <thead>
            <tr>
                <th>Common Name</th>
                <th>Scientific Name</th>
                <th>Family</th>
                <th>Local Name</th>
                <th>Conservation</th>
                <th>Origin</th>
                <th>Trees</th>
                <th>Active</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($species as $speciesRecord)
                <tr>
                    <td>{{ $speciesRecord->common_name }}</td>
                    <td><em>{{ $speciesRecord->scientific_name }}</em></td>
                    <td>{{ $speciesRecord->family_name }}</td>
                    <td>{{ $speciesRecord->local_name ?: '—' }}</td>
                    <td>{{ Illuminate\Support\Str::headline($speciesRecord->conservation_status->value) }}</td>
                    <td>{{ $speciesRecord->origin ?: '—' }}</td>
                    <td>{{ $speciesRecord->trees_count }}</td>
                    <td>{{ $speciesRecord->is_active ? 'Yes' : 'No' }}</td>
                </tr>
            @empty
                <tr><td class="empty-row" colspan="8">No records match the current filters.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
