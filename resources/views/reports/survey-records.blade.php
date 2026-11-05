<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NVSU Survey Records Report</title>
    <link rel="stylesheet" href="{{ public_path('css/reports.css') }}">
</head>
<body>
    <header class="report-header">
        <h1>NVSU Survey Records Report</h1>
        <p>Generated {{ $generatedAt->format('F j, Y g:i A') }} · Current filtered records</p>
    </header>

    <p class="report-summary"><strong>{{ $surveyPoints->count() }}</strong> survey records included.</p>

    <table>
        <thead>
            <tr>
                <th>Survey ID</th>
                <th>Recorded Species</th>
                <th>Source</th>
                <th>Height</th>
                <th>Coordinates</th>
                <th>Coordinate Status</th>
                <th>Review Status</th>
                <th>Registered Tree</th>
                <th>Reviewer</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($surveyPoints as $surveyPoint)
                <tr>
                    <td class="nowrap">S-{{ str_pad((string) $surveyPoint->source_row, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $surveyPoint->raw_species_name ?: 'Unidentified tree' }}</td>
                    <td>{{ $surveyPoint->source_file }} · row {{ $surveyPoint->source_row }}</td>
                    <td class="nowrap">{{ $surveyPoint->height_m ? $surveyPoint->height_m.' m' : '—' }}</td>
                    <td class="nowrap">{{ $surveyPoint->latitude }}, {{ $surveyPoint->longitude }}</td>
                    <td>{{ Illuminate\Support\Str::headline($surveyPoint->coordinate_status) }}</td>
                    <td>{{ Illuminate\Support\Str::headline($surveyPoint->review_status->value) }}</td>
                    <td>{{ $surveyPoint->registeredTree?->tree_code ?: '—' }}</td>
                    <td>{{ $surveyPoint->reviewer?->name ?: '—' }}</td>
                </tr>
            @empty
                <tr><td class="empty-row" colspan="9">No records match the current filters.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
