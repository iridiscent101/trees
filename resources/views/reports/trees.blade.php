<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NVSU Tree Inventory Report</title>
    <link rel="stylesheet" href="{{ public_path('css/reports.css') }}">
</head>
<body>
    <header class="report-header">
        <h1>NVSU Tree Inventory Report</h1>
        <p>Generated {{ $generatedAt->format('F j, Y g:i A') }} · Current filtered records</p>
    </header>

    <p class="report-summary"><strong>{{ $trees->count() }}</strong> tree records included.</p>

    <table>
        <thead>
            <tr>
                <th>Tree ID</th>
                <th>Species</th>
                <th>Campus Area</th>
                <th>Location</th>
                <th>Coordinates</th>
                <th>Height</th>
                <th>Condition</th>
                <th>Verification</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($trees as $tree)
                <tr>
                    <td class="nowrap">{{ $tree->tree_code }}</td>
                    <td>{{ $tree->species?->common_name }}<br><em>{{ $tree->species?->scientific_name }}</em></td>
                    <td>{{ $tree->campusArea?->name }}</td>
                    <td>{{ $tree->descriptive_location }}</td>
                    <td class="nowrap">{{ $tree->latitude }}, {{ $tree->longitude }}</td>
                    <td class="nowrap">{{ $tree->estimated_height_m ? $tree->estimated_height_m.' m' : '—' }}</td>
                    <td>{{ Illuminate\Support\Str::headline($tree->current_condition->value) }}</td>
                    <td>{{ Illuminate\Support\Str::headline($tree->verification_status->value) }}</td>
                </tr>
            @empty
                <tr><td class="empty-row" colspan="8">No records match the current filters.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
