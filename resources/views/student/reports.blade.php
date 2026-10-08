@extends('layouts.app')
@section('title', 'Student Report | GradeGenius')
@section('content')
@include('partials.report-header')
<main class="gg-report">
    <h1>Your Academic Report</h1>
    <p class="gg-report-intro">Track your marks, assessments, and learning progress.</p>

    <div class="gg-metric-grid">
        @foreach ([['Current Average', '74%', 'Term 4', ''], ['Improvement', '+16%', 'Since Term 1', 'green'], ['Subjects', '5', '', 'orange'], ['Target Gap', 'On track', '10%+ goal met', 'blue']] as $metric)
            <div class="gg-card"><div class="gg-card-label">{{ $metric[0] }}</div><div class="gg-card-value gg-value-{{ $metric[3] }}">{{ $metric[1] }}</div><div class="gg-card-sub">{{ $metric[2] }}</div></div>
        @endforeach
    </div>

    <section class="gg-panel gg-chart-panel">
        <div class="gg-panel-heading">@include('partials.icon', ['name' => 'trending-up', 'size' => 20])<h2>Progress Over Time</h2></div>
        <svg class="gg-chart" viewBox="0 0 800 220" role="img" aria-label="Progress increased from 58 percent in Term 1 to 74 percent in Term 4">
            <defs><linearGradient id="progress-fill" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#ff6435" stop-opacity=".35"/><stop offset="1" stop-color="#ff6435" stop-opacity="0"/></linearGradient></defs>
            <path class="gg-chart-grid" d="M50 25H780M50 75H780M50 125H780M50 175H780M50 25V175M293 25V175M536 25V175M780 25V175"/>
            <path class="gg-chart-area" d="M50 130L293 110L536 90L780 70V175H50Z"/>
            <path class="gg-chart-line gg-chart-line-orange" d="M50 130L293 110L536 90L780 70"/>
            @foreach ([['x' => 50, 'label' => 'Term 1', 'value' => 58], ['x' => 293, 'label' => 'Term 2', 'value' => 64], ['x' => 536, 'label' => 'Term 3', 'value' => 69], ['x' => 780, 'label' => 'Term 4', 'value' => 74]] as $point)
                <circle class="gg-chart-point gg-chart-point-orange" cx="{{ $point['x'] }}" cy="{{ 175 - (($point['value'] - 40) * 2.5) }}" r="4"/>
                <text x="{{ $point['x'] }}" y="205" text-anchor="middle">{{ $point['label'] }}</text>
            @endforeach
            <text x="38" y="30" text-anchor="end">100</text><text x="38" y="80" text-anchor="end">85</text><text x="38" y="130" text-anchor="end">70</text><text x="38" y="180" text-anchor="end">40</text>
        </svg>
    </section>

    <section class="gg-panel">
        <div class="gg-panel-heading">@include('partials.icon', ['name' => 'file', 'size' => 20])<h2>Assessment Performance</h2></div>
        <div class="gg-metric-grid gg-metric-grid-compact">
            @foreach ([['Quizzes completed', '37', 'orange'], ['Past papers', '12', 'blue'], ['Average score', '76%', ''], ['Quiz accuracy', '81%', 'green'], ['Questions answered', '486', 'orange']] as $stat)
                <div class="gg-card"><div class="gg-card-label">{{ $stat[0] }}</div><div class="gg-card-value gg-value-{{ $stat[2] }}">{{ $stat[1] }}</div></div>
            @endforeach
        </div>
        <h3 class="gg-subheading">Recent Assessments</h3>
        <div class="gg-table-wrap"><table class="gg-table"><thead><tr><th>Assessment</th><th>Type</th><th>Subject</th><th>Score</th><th>Date</th></tr></thead><tbody>
            @foreach ($assessments as $assessment)
                <tr><td>{{ $assessment['name'] }}</td><td><span class="gg-pill gg-pill-{{ strtolower(str_replace(' ', '-', $assessment['type'])) }}">{{ $assessment['type'] }}</span></td><td>{{ $assessment['subject'] }}</td><td><strong>{{ $assessment['score'] }}%</strong></td><td>{{ $assessment['date'] }}</td></tr>
            @endforeach
        </tbody></table></div>
    </section>

    <section class="gg-panel">
        <div class="gg-panel-heading">@include('partials.icon', ['name' => 'bar-chart', 'size' => 20])<h2>Quiz Analytics</h2></div>
        <div class="gg-stat-grid">@foreach ([['Completed', '37', 'orange'], ['Average', '81%', ''], ['Highest', '96%', 'green'], ['Lowest', '52%', 'red'], ['Avg attempts', '1.8', 'blue'], ['Accuracy', '79%', 'orange']] as $stat)<div class="gg-inline-stat"><strong class="gg-value-{{ $stat[2] }}">{{ $stat[1] }}</strong><span>{{ $stat[0] }}</span></div>@endforeach</div>
        <svg class="gg-chart gg-chart-blue" viewBox="0 0 800 220" role="img" aria-label="Quiz scores improved from 65 to 90 percent">
            <path class="gg-chart-grid" d="M50 25H780M50 75H780M50 125H780M50 175H780M50 25V175M171 25V175M293 25V175M414 25V175M536 25V175M657 25V175M780 25V175"/>
            <polyline class="gg-chart-line gg-chart-line-blue" points="50,142 171,125 293,135 414,108 536,75 657,85 780,58"/>
            @foreach ($quizScores as $index => $score)
                <circle class="gg-chart-point gg-chart-point-blue" cx="{{ 50 + ($index * 121.67) }}" cy="{{ 175 - (($score - 40) * 2.5) }}" r="4"/>
                <text x="{{ 50 + ($index * 121.67) }}" y="205" text-anchor="middle">Q{{ $index + 1 }}</text>
            @endforeach
            <text x="38" y="30" text-anchor="end">100</text><text x="38" y="80" text-anchor="end">85</text><text x="38" y="130" text-anchor="end">70</text><text x="38" y="180" text-anchor="end">40</text>
        </svg>
        <details class="gg-quiz-performance">
            <summary>Check quiz performance</summary>
            <div class="gg-quiz-performance-list">
                @foreach ($quizPerformanceBySubject as $performance)
                    <div class="gg-quiz-performance-row">
                        <strong>{{ $performance['subject'] }}</strong>
                        <span>Lowest <b class="gg-value-red">{{ $performance['lowest'] }}%</b></span>
                        <span>Highest <b class="gg-value-green">{{ $performance['highest'] }}%</b></span>
                    </div>
                @endforeach
            </div>
        </details>
    </section>

    <section class="gg-panel">
        <div class="gg-panel-heading">@include('partials.icon', ['name' => 'file', 'size' => 20])<h2>Past Paper Performance</h2></div>
        <div class="gg-subject-switcher" role="tablist" aria-label="Past paper subjects">
            @foreach ($pastPaperPerformance as $subject => $performance)
                <button class="gg-subject-tab{{ $loop->first ? ' active' : '' }}" type="button" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-subject-tab="{{ $loop->index }}">{{ $subject }}</button>
            @endforeach
        </div>
        @foreach ($pastPaperPerformance as $subject => $performance)
            <div class="gg-past-paper-content{{ $loop->first ? ' active' : '' }}" data-subject-panel="{{ $loop->index }}" role="tabpanel">
                <h3 class="gg-subheading">{{ $subject }}</h3>
                <div class="gg-past-paper-grid"><div class="gg-bars">@foreach ($performance['papers'] as $paper)<div class="gg-bar-row"><span>{{ $paper['name'] }}</span><div class="gg-bar"><span style="width: {{ $paper['score'] }}%"></span></div><strong>{{ $paper['score'] }}%</strong></div>@endforeach<div class="gg-paper-average"><strong>Average</strong><b>{{ $performance['average'] }}%</b></div></div><div class="gg-paper-analysis"><span>Questions attempted: <strong>{{ $performance['attempted'] }}</strong></span><div class="gg-correct-grid"><div><strong>{{ $performance['correct'] }}</strong><small>Correct ({{ round(($performance['correct'] / $performance['attempted']) * 100) }}%)</small></div><div><strong>{{ $performance['incorrect'] }}</strong><small>Incorrect ({{ round(($performance['incorrect'] / $performance['attempted']) * 100) }}%)</small></div></div><h4>Strongest</h4><p class="gg-success-text">@foreach ($performance['strongest'] as $topic)✓ {{ $topic }}<br>@endforeach</p><h4>Needs attention</h4><p class="gg-warning-text">@foreach ($performance['attention'] as $topic)⚠ {{ $topic }}<br>@endforeach</p></div></div>
            </div>
        @endforeach
    </section>

    <section class="gg-panel">
        <div class="gg-panel-heading"><h2>Topic Mastery</h2></div>
        <details class="gg-topic" open><summary>Mathematics <span>⌃</span></summary><div class="gg-bars">@foreach ($topicMastery as $topic)<div class="gg-bar-row"><span>{{ $topic['name'] }}</span><div class="gg-bar gg-bar-topic"><span style="width: {{ $topic['mark'] }}%"></span></div><strong>{{ $topic['mark'] }}%</strong></div>@endforeach</div></details>
        <details class="gg-topic"><summary>Physical Sciences <span>⌄</span></summary><p class="gg-card-sub">Open to view topic performance.</p></details>
        <details class="gg-topic"><summary>English <span>⌄</span></summary><p class="gg-card-sub">Open to view topic performance.</p></details>
    </section>

    <section class="gg-panel">
        <div class="gg-panel-heading">@include('partials.icon', ['name' => 'clock', 'size' => 20])<h2>Study Activity — This Week</h2><span class="gg-streak">@include('partials.icon', ['name' => 'zap', 'size' => 14]) 7 day streak</span></div>
        <div class="gg-stat-grid gg-study-stats">@foreach ([['Study time', '8h 42m', ''], ['Sessions', '14', 'blue'], ['Questions', '186', 'orange'], ['Quizzes', '7', 'orange'], ['Past papers', '2', 'blue'], ['Topics done', '9', 'green']] as $stat)<div class="gg-inline-stat"><strong class="gg-value-{{ $stat[2] }}">{{ $stat[1] }}</strong><span>{{ $stat[0] }}</span></div>@endforeach</div>
        <div class="gg-activity gg-activity-chart">@foreach ($studyActivity as $day)<div class="gg-activity-bar"><span style="height: {{ ($day['minutes'] / 120) * 100 }}%"></span><small>{{ $day['day'] }}</small></div>@endforeach</div>
    </section>

    <section class="gg-insight"><div class="gg-panel-heading">@include('partials.icon', ['name' => 'target', 'size' => 20])<h2>Accuracy vs Time Insight</h2></div><p>You are answering <strong>Trigonometry</strong> questions quickly, but your accuracy is only <strong>54%</strong>. Consider slowing down and reviewing the worked examples.</p><div class="gg-insight-grid"><div>◷ <strong>Efficient</strong><small>Fast + High accuracy</small></div><div>◷ <strong>Rushing</strong><small>Fast + Low accuracy</small></div><div>◷ <strong>Strong</strong><small>Slow + High accuracy</small></div><div>◷ <strong>Struggling</strong><small>Slow + Low accuracy</small></div></div></section>

    <section class="gg-panel"><div class="gg-panel-heading">@include('partials.icon', ['name' => 'target', 'size' => 20])<h2>Exam Readiness</h2></div><div class="gg-bars">@foreach ($readiness as $item)<div class="gg-bar-row"><span>{{ $item['name'] }}</span><div class="gg-bar gg-bar-blue"><span style="width: {{ $item['mark'] }}%"></span></div><strong>{{ $item['mark'] }}%</strong></div>@endforeach</div><hr class="gg-divider"><div class="gg-readiness-title"><h3>Overall Readiness</h3><b>78%</b></div><div class="gg-factor-list">@foreach ([['78%', 'Subject Mastery'], ['74%', 'Past Paper Performance'], ['81%', 'Quiz Performance'], ['+9%', 'Recent Improvement'], ['83%', 'Topic Coverage']] as $factor)<div><strong>{{ $factor[0] }}</strong><span>{{ $factor[1] }}</span></div>@endforeach</div><div class="gg-readiness-banner">Estimated readiness: On track</div></section>

    {{-- <section class="gg-panel"><div class="gg-panel-heading">@include('partials.icon', ['name' => 'target', 'size' => 20])<h2>My Goals</h2></div><div class="gg-goal-list">@foreach ($goals as $goal)<div class="gg-goal"><div><strong>{{ $goal['name'] }}</strong><span>Current: <b>{{ $goal['current'] }}</b> / Target: <b>{{ $goal['target'] }}</b></span></div><div class="gg-bar"><span style="width: {{ $goal['progress'] }}%"></span></div><small>{{ $goal['detail'] }}</small><b>{{ $goal['progress'] }}% completed</b></div>@endforeach</div></section> --}}

    {{-- <section class="gg-panel gg-recommendations"><div class="gg-panel-heading"><h2>Recommended Next Steps</h2></div>@foreach ([['Revise Probability', 'Your average on Probability questions is 51%.', 'Start Revision'], ['Complete 2024 Mathematics Paper 1', 'Your recent Mathematics average is 76%.', 'Start Paper'], ['Practice Chemical Reactions', "You've answered 42 questions with 64% accuracy.", 'Practice Topic']] as $index => $recommendation)<div class="gg-recommendation"><span>{{ $index + 1 }}</span><div><strong>{{ $recommendation[0] }}</strong><small>{{ $recommendation[1] }}</small></div><a class="gg-button gg-button-blue" href="#">{{ $recommendation[2] }} @include('partials.icon', ['name' => 'arrow-right', 'size' => 15])</a></div>@endforeach</section> --}}

    {{-- <section class="gg-panel"><div class="gg-panel-heading"><h2>Achievements</h2></div><div class="gg-achievements">@foreach ($achievements as $achievement)<div>@include('partials.icon', ['name' => $achievement['icon'], 'size' => 28])<span>{{ $achievement['name'] }}</span><small>✓ Achieved</small></div>@endforeach</div></section> --}}

    <section class="gg-panel"><div class="gg-panel-heading"><h2>Assessment Results</h2></div><div class="gg-results">@foreach ($results as $result)<details class="gg-result-row"><summary><span><strong>{{ $result['name'] }}</strong><small>{{ $result['date'] }}</small></span><b>{{ $result['score'] }}</b><i>›</i></summary><div class="gg-result-detail"><p><strong>Result breakdown</strong></p><span>Correct: 42</span><span>Incorrect: 8</span><span>Accuracy: {{ $result['score'] }}</span><span>Time: 24 min</span></div></details>@endforeach</div></section>
</main>
<script>
    document.querySelectorAll('[data-subject-tab]').forEach((tab) => {
        tab.addEventListener('click', () => {
            const subjectIndex = tab.dataset.subjectTab;

            document.querySelectorAll('[data-subject-tab]').forEach((item) => {
                item.classList.toggle('active', item === tab);
                item.setAttribute('aria-selected', item === tab ? 'true' : 'false');
            });
            document.querySelectorAll('[data-subject-panel]').forEach((panel) => {
                panel.classList.toggle('active', panel.dataset.subjectPanel === subjectIndex);
            });
        });
    });
</script>
@endsection
