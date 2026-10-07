@extends('layouts.app')
@section('title', 'Student Report | GradeGenius')
@section('content')
@include('partials.report-header')
<main class="gg-report">
    <h1>Your Learning Report</h1>
    <p class="gg-report-intro">See how your study activity, assessments, and practice are building exam readiness.</p>

    <div class="gg-metric-grid">
        @foreach ([['Current Average', '74%', 'Term 4', ''], ['Improvement', '+16%', 'Since Term 1', 'green'], ['Subjects', '5', 'being tracked', 'orange'], ['Target Gap', 'On track', '10%+ goal met', 'blue']] as $metric)
            <div class="gg-card"><div class="gg-card-label">{{ $metric[0] }}</div><div class="gg-card-value gg-value-{{ $metric[3] }}">{{ $metric[1] }}</div><div class="gg-card-sub">{{ $metric[2] }}</div></div>
        @endforeach
    </div>

    <div class="gg-panel">
        <div class="gg-panel-heading">@include('partials.icon', ['name' => 'file', 'size' => 20])<h2>Assessment Performance</h2></div>
        <div class="gg-metric-grid gg-metric-grid-compact">
            @foreach ([['Quizzes completed', '37'], ['Past papers', '12'], ['Average score', '76%'], ['Quiz accuracy', '81%'], ['Questions answered', '486']] as $stat)
                <div class="gg-card"><div class="gg-card-label">{{ $stat[0] }}</div><div class="gg-card-value">{{ $stat[1] }}</div></div>
            @endforeach
        </div>
        <div class="gg-table-wrap">
            <table class="gg-table"><thead><tr><th>Assessment</th><th>Type</th><th>Subject</th><th>Score</th><th>Date</th></tr></thead><tbody>
                @foreach ($assessments as $assessment)
                    <tr><td>{{ $assessment['name'] }}</td><td><span class="gg-pill">{{ $assessment['type'] }}</span></td><td>{{ $assessment['subject'] }}</td><td><strong>{{ $assessment['score'] }}%</strong></td><td>{{ $assessment['date'] }}</td></tr>
                @endforeach
            </tbody></table>
        </div>
    </div>

    <div class="gg-report-grid">
        <div class="gg-panel">
            <div class="gg-panel-heading">@include('partials.icon', ['name' => 'trending-up', 'size' => 20])<h2>Progress Over Time</h2></div>
            <div class="gg-bars">@foreach ($progress as $item)<div class="gg-bar-row"><span>{{ $item['term'] }}</span><div class="gg-bar"><span style="width: {{ $item['mark'] }}%"></span></div><strong>{{ $item['mark'] }}%</strong></div>@endforeach</div>
        </div>
        <div class="gg-panel">
            <div class="gg-panel-heading">@include('partials.icon', ['name' => 'clock', 'size' => 20])<h2>Study Activity This Week</h2></div>
            <div class="gg-activity-summary"><strong>8h 42m</strong><span>14 sessions · 186 questions</span></div>
            <div class="gg-activity">@foreach ($studyActivity as $day)<div class="gg-activity-row"><span>{{ $day['day'] }}</span><div class="gg-bar"><span style="width: {{ ($day['minutes'] / 102) * 100 }}%"></span></div><small>{{ $day['time'] }}</small></div>@endforeach</div>
        </div>
    </div>

    <div class="gg-report-grid">
        <div class="gg-panel">
            <div class="gg-panel-heading">@include('partials.icon', ['name' => 'book-open', 'size' => 20])<h2>Subject Breakdown</h2></div>
            <div class="gg-bars">@foreach ($subjects as $subject)<div class="gg-bar-row"><span>{{ $subject['name'] }}<small class="gg-card-sub">Grade {{ $subject['grade'] }}</small></span><div class="gg-bar"><span style="width: {{ $subject['mark'] }}%"></span></div><strong>{{ $subject['mark'] }}%</strong></div>@endforeach</div>
        </div>
        <div class="gg-panel">
            <div class="gg-panel-heading">@include('partials.icon', ['name' => 'target', 'size' => 20])<h2>Mathematics Topic Mastery</h2></div>
            <div class="gg-bars">@foreach ($topicMastery as $topic)<div class="gg-bar-row"><span>{{ $topic['name'] }}</span><div class="gg-bar"><span style="width: {{ $topic['mark'] }}%"></span></div><strong>{{ $topic['mark'] }}%</strong></div>@endforeach</div>
        </div>
    </div>

    <div class="gg-panel">
        <div class="gg-panel-heading">@include('partials.icon', ['name' => 'bar-chart', 'size' => 20])<h2>Quiz Analytics</h2></div>
        <div class="gg-stat-grid">@foreach ($quizStats as $stat)<div class="gg-inline-stat"><strong>{{ $stat['value'] }}</strong><span>{{ $stat['label'] }}</span></div>@endforeach</div>
    </div>

    <div class="gg-panel">
        <div class="gg-panel-heading">@include('partials.icon', ['name' => 'target', 'size' => 20])<h2>Exam Readiness</h2></div>
        <div class="gg-readiness-grid"><div><div class="gg-bars">@foreach ($readiness as $item)<div class="gg-bar-row"><span>{{ $item['name'] }}</span><div class="gg-bar"><span style="width: {{ $item['mark'] }}%"></span></div><strong>{{ $item['mark'] }}%</strong></div>@endforeach</div></div><div class="gg-readiness-score"><strong>78%</strong><span>Overall readiness</span><b>On track</b></div></div>
        <div class="gg-factor-list"><span>Subject mastery <strong>78%</strong></span><span>Past paper performance <strong>74%</strong></span><span>Quiz performance <strong>81%</strong></span><span>Topic coverage <strong>83%</strong></span></div>
    </div>

    <div class="gg-panel">
        <div class="gg-panel-heading">@include('partials.icon', ['name' => 'target', 'size' => 20])<h2>My Goals</h2></div>
        <div class="gg-goal-grid">@foreach ($goals as $goal)<div class="gg-goal"><div><strong>{{ $goal['name'] }}</strong><span>{{ $goal['current'] }} of {{ $goal['target'] }}</span></div><div class="gg-bar"><span style="width: {{ $goal['progress'] }}%"></span></div><small>{{ $goal['detail'] }}</small></div>@endforeach</div>
    </div>

    <div class="gg-note">@include('partials.icon', ['name' => 'sparkles', 'size' => 20])<div><strong>What should you do next?</strong><p>Revise Probability (51% mastery), then complete the 2024 Mathematics Paper 1. Your recent Mathematics average is 76% and targeted practice can close the gap to your 80% goal.</p><div class="gg-actions-left"><a class="gg-button gg-button-blue" href="#">Start revision</a><a class="gg-button gg-button-surface" href="#">Start paper</a></div></div></div>

    <div class="gg-panel">
        <div class="gg-panel-heading">@include('partials.icon', ['name' => 'check-circle', 'size' => 20])<h2>Results History</h2></div>
        <div class="gg-results">@foreach ($results as $result)<div class="gg-result-row"><span><strong>{{ $result['name'] }}</strong><small>{{ $result['date'] }}</small></span><b>{{ $result['score'] }}</b><a href="#">View</a></div>@endforeach</div>
    </div>
</main>
@endsection
