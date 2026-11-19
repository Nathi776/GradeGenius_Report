@extends('layouts.teacher', ['title' => 'Teacher engagement dashboard', 'active' => 'home'])

@section('teacher-content')
<div class="teacher-engagement-dashboard min-h-screen" data-teacher-dashboard data-teacher-name="{{ $dashboard['teacher']['name'] }}" data-selected-term="{{ $selectedTerm }}" data-last-term-index="{{ count($terms) - 1 }}" data-learner-presence='@json($learnerPresence)'>
  @include('partials.print-header', [
      'title' => 'Teacher Academic Engagement Report',
      'term' => $selectedTerm === 'all' ? 'All Terms' : $terms[$selectedTermIndex],
  ])
  <!-- Toast Notification Banner -->
  <div id="toastBanner" class="fixed bottom-5 right-5 z-50 hidden transform transition-all duration-300">
    <div class="bg-mint border-2 border-black shadow-brutal rounded-xl px-4 py-3 flex items-center gap-3 font-semibold text-sm">
      <span class="w-6 h-6 rounded-full bg-black text-white flex items-center justify-center text-xs">✓</span>
      <span id="toastMessage">Action completed!</span>
    </div>
  </div>

  <!-- Top Navigation / Teacher Header -->
  <header class="no-print bg-white border-b-2 border-black sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <!-- Brand & Educator Identity -->
      <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-butter border-2 border-black shadow-brutal-sm flex items-center justify-center font-display font-extrabold text-xl">
          {{ strtoupper(substr($dashboard['teacher']['name'], 0, 2)) }}
        </div>
        <div>
          <div class="flex flex-wrap items-center gap-2">
            <h1 class="font-display font-extrabold text-xl sm:text-2xl tracking-tight">Welcome back, {{ $dashboard['teacher']['name'] }}</h1>
            <span class="px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider bg-sky border-2 border-black rounded-full">
              Educator Portal
            </span>
            <span class="px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider bg-coral border-2 border-black rounded-full">
              Term 4 sample engagement
            </span>
          </div>
          <p class="text-xs sm:text-sm text-neutral-600 font-medium mt-0.5">
            Teacher workspace • GradeGenius Reports
          </p>
        </div>
      </div>

      <!-- Academic Term Filter & Quick Status -->
      <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2 rounded-xl border-2 border-black bg-white px-3 py-1.5 shadow-brutal-sm">
          <span class="text-xs font-bold uppercase text-neutral-500">Theme:</span>
          @include('partials.theme-toggle')
        </div>
        <div class="flex items-center gap-2 bg-cream border-2 border-black rounded-xl px-3 py-1.5 shadow-brutal-sm">
          <span class="text-xs font-bold uppercase text-neutral-500">Term:</span>
          <select id="termSelector" onchange="window.location.assign(this.value)" class="bg-transparent font-display font-bold text-sm focus:outline-none cursor-pointer">
            <option value="{{ request()->fullUrlWithQuery(['term' => 'all']) }}" @selected($selectedTerm === 'all')>All terms</option>
            @foreach ($terms as $index => $term)
              <option value="{{ request()->fullUrlWithQuery(['term' => (string) $index]) }}" @selected($selectedTerm === (string) $index)>{{ $term }}{{ $index === count($terms) - 1 ? ' (Current)' : '' }}</option>
            @endforeach
          </select>
        </div>
        <button onclick="showToast('Sample engagement data is up to date.')" class="brutal-btn bg-mint hover:bg-emerald-300 px-3.5 py-2 rounded-xl font-display font-bold text-xs sm:text-sm flex items-center gap-2 cursor-pointer">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span>Sync Reports</span>
        </button>
      </div>
    </div>
  </header>

  <!-- Main Container -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-8">

    <!-- High-Level Overview KPI Cards -->
    <section aria-label="Educator Overview Metrics">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- KPI 1 -->
        <div class="brutal-card bg-white rounded-2xl p-4 flex items-center justify-between">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-neutral-500">Subjects Taught</p>
            <p id="kpiTotalSubjects" class="font-display font-extrabold text-3xl mt-1">3</p>
            <p class="text-xs font-semibold text-neutral-600 mt-1">Grade 11 Curriculum</p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-butter border-2 border-black flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
        </div>

        <!-- KPI 2 -->
        <div class="brutal-card bg-white rounded-2xl p-4 flex items-center justify-between">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-neutral-500">Total Active Learners</p>
            <p id="kpiTotalLearners" class="font-display font-extrabold text-3xl mt-1">24</p>
            <p class="text-xs font-semibold text-emerald-700 mt-1">Across all 3 classes</p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-sky border-2 border-black flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
        </div>

        <!-- KPI 3 -->
        <div data-term-four-only @if ($selectedTerm !== 'all' && $selectedTermIndex !== count($terms) - 1) hidden @endif class="brutal-card bg-white rounded-2xl p-4 flex items-center justify-between">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-neutral-500">Avg. Quiz Score</p>
            <p id="kpiAvgQuiz" class="font-display font-extrabold text-3xl mt-1">74.8%</p>
            <p id="kpiAvgQuizSub" class="text-xs font-semibold text-neutral-600 mt-1">Selected subject avg</p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-mint border-2 border-black flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>

        <!-- KPI 4 -->
        <div data-term-four-only @if ($selectedTerm !== 'all' && $selectedTermIndex !== count($terms) - 1) hidden @endif class="brutal-card bg-white rounded-2xl p-4 flex items-center justify-between">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-neutral-500">Notes Engagement</p>
            <p id="kpiNotesRate" class="font-display font-extrabold text-3xl mt-1">86%</p>
            <p id="kpiNotesSub" class="text-xs font-semibold text-neutral-600 mt-1">Study guides opened</p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-coral border-2 border-black flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
        </div>
      </div>
    </section>

    <section aria-label="Class marks by term" class="brutal-card bg-white rounded-2xl p-4 sm:p-5">
      <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
          <h2 class="font-display font-extrabold text-lg">Class average by term</h2>
          <p class="text-xs text-neutral-600">Mark history is available across all four terms.</p>
        </div>
        <p class="rounded-xl border-2 border-black bg-butter px-3 py-1.5 text-sm font-bold">
          {{ $selectedTerm === 'all' ? 'Latest term' : $terms[$selectedTermIndex] }}:
          {{ $dashboard['trend']['points'][$selectedTermIndex] ?? 0 }}%
        </p>
      </div>
      @if ($selectedTerm === 'all')
        <p class="mt-3 text-xs text-neutral-600">Quiz and study-note engagement shown below is Term 4 sample data; earlier-term engagement records are unavailable.</p>
      @endif
      <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach ($dashboard['trend']['labels'] as $index => $term)
          <div class="rounded-xl border-2 {{ $selectedTermIndex === $index ? 'border-black bg-butter' : 'border-black/30 bg-cream' }} p-3 text-center">
            <p class="text-xs font-bold uppercase">{{ $term }}</p>
            <p class="font-display text-xl font-extrabold">{{ $dashboard['trend']['points'][$index] }}%</p>
          </div>
        @endforeach
      </div>
    </section>

    <p data-term-unavailable @if ($selectedTerm === 'all' || $selectedTermIndex === count($terms) - 1) hidden @endif class="rounded-xl border-2 border-black bg-sky p-4 text-sm font-semibold shadow-brutal-sm">
      Quiz scores, learner activity, and study-note engagement are only available for Term 4 in this sample. The class-mark history above remains available for all terms.
    </p>

    <!-- Subjects Taught Selector -->
    <section data-term-four-only @if ($selectedTerm !== 'all' && $selectedTermIndex !== count($terms) - 1) hidden @endif aria-label="Subjects Taught Selector" class="space-y-3">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
          <h2 class="font-display font-extrabold text-lg sm:text-xl flex items-center gap-2">
            <span>1. Select a Subject You Teach</span>
            <span class="px-2 py-0.5 text-xs font-bold bg-butter border-2 border-black rounded-md">Interactive</span>
          </h2>
          <p class="text-xs sm:text-sm text-neutral-600">Click any subject card below to inspect enrolled students, quizzes, notes engagement, and individual student reports.</p>
        </div>
      </div>

      <div id="subjectsGrid" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Subject cards rendered dynamically via JS -->
      </div>
    </section>

    <!-- Selected Subject Workspace -->
    <section data-term-four-only @if ($selectedTerm !== 'all' && $selectedTermIndex !== count($terms) - 1) hidden @endif aria-label="Selected Subject Workspace" class="brutal-card bg-white rounded-3xl overflow-hidden">
      <!-- Workspace Top Header -->
      <div id="workspaceHeader" class="p-5 sm:p-6 border-b-2 border-black bg-cream flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
          <div class="flex flex-wrap items-center gap-2">
            <span id="activeSubjectCode" class="px-2.5 py-1 text-xs font-extrabold uppercase bg-black text-white rounded-lg">TMATH-11</span>
            <span id="activeSubjectGrade" class="px-2.5 py-1 text-xs font-bold bg-sky border-2 border-black rounded-lg">Grade 11</span>
            <span class="px-2.5 py-1 text-xs font-bold bg-mint border-2 border-black rounded-lg flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-black animate-pulse"></span>
              Active Workspace
            </span>
          </div>
          <h3 id="activeSubjectName" class="font-display font-extrabold text-2xl sm:text-3xl mt-2">Technical Mathematics</h3>
          <p id="activeSubjectDesc" class="text-xs sm:text-sm text-neutral-600 mt-1">Analytical Geometry, Mensuration & Trigonometry • Set Quizzes & Study Notes Tracking</p>
        </div>

        <!-- Workspace View Switcher Tabs -->
        <div class="flex flex-wrap gap-2 bg-white p-1.5 border-2 border-black rounded-2xl shadow-brutal-sm self-start lg:self-center">
          <button id="tabStudentsBtn" onclick="switchWorkspaceTab('students')" class="px-4 py-2 rounded-xl font-display font-bold text-xs sm:text-sm transition-all cursor-pointer flex items-center gap-2 bg-butter border-2 border-black shadow-brutal-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>Enrolled Students (<span id="tabStudentCount">8</span>)</span>
          </button>

          <button id="tabContentBtn" onclick="switchWorkspaceTab('content')" class="px-4 py-2 rounded-xl font-display font-bold text-xs sm:text-sm transition-all cursor-pointer flex items-center gap-2 text-neutral-700 hover:bg-cream border-2 border-transparent">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Quizzes & Notes Report (<span id="tabResourceCount">8</span>)</span>
          </button>
        </div>
      </div>

      <!-- TAB 1: Enrolled Students & Individual Performance List -->
      <div id="studentsTabPanel" class="p-5 sm:p-6 space-y-5">
        <!-- Search & Filter Controls -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
          <!-- Search Input -->
          <div class="relative flex-1 max-w-md">
            <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              type="text"
              id="studentSearchInput"
              oninput="renderStudentsTable()"
              placeholder="Search student by name or ID..."
              class="w-full pl-10 pr-4 py-2.5 bg-cream border-2 border-black rounded-xl text-sm font-medium focus:outline-none focus:bg-white focus:shadow-brutal-sm transition-all"
            />
          </div>

          <!-- Performance Filter Pills -->
          <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold uppercase text-neutral-500 mr-1">Filter:</span>
            <button onclick="setPerformanceFilter('all')" data-filter="all" class="filter-pill px-3 py-1.5 rounded-xl text-xs font-bold border-2 border-black bg-black text-white cursor-pointer">
              All Learners
            </button>
            <button onclick="setPerformanceFilter('excelling')" data-filter="excelling" class="filter-pill px-3 py-1.5 rounded-xl text-xs font-bold border-2 border-black bg-mint text-black cursor-pointer">
              Excelling (≥75%)
            </button>
            <button onclick="setPerformanceFilter('ontrack')" data-filter="ontrack" class="filter-pill px-3 py-1.5 rounded-xl text-xs font-bold border-2 border-black bg-sky text-black cursor-pointer">
              On Track (50–74%)
            </button>
            <button onclick="setPerformanceFilter('attention')" data-filter="attention" class="filter-pill px-3 py-1.5 rounded-xl text-xs font-bold border-2 border-black bg-coral text-black cursor-pointer">
              Needs Attention (&lt;50%)
            </button>
          </div>
        </div>
        <p class="text-xs text-neutral-600">Online means an authenticated student session was active within the last 5 minutes. Unlinked sample learners are marked as demo.</p>

        <!-- Students Responsive Table -->
        <div class="overflow-x-auto border-2 border-black rounded-2xl">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-cream border-b-2 border-black text-xs font-display font-extrabold uppercase tracking-wider">
                <th class="py-3.5 px-4">Student</th>
                <th class="py-3.5 px-4">Quizzes Completed</th>
                <th class="py-3.5 px-4">Avg. Quiz Score</th>
                <th class="py-3.5 px-4">Notes Read</th>
                <th class="py-3.5 px-4">Learning Status</th>
                <th class="py-3.5 px-4">Presence</th>
                <th class="py-3.5 px-4">Last Active</th>
                <th class="py-3.5 px-4 text-right">Subject Report</th>
              </tr>
            </thead>
            <tbody id="studentsTableBody" class="divide-y-2 divide-black/15 text-sm">
              <!-- Rows injected by JS -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 2: Quizzes & Study Notes Engagement Report -->
      <div id="contentTabPanel" class="hidden p-5 sm:p-6 space-y-8">
        <!-- Quizzes Section -->
        <div class="space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b-2 border-black pb-3">
            <div>
              <h4 class="font-display font-extrabold text-lg sm:text-xl flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-butter border-2 border-black inline-block"></span>
                <span>Quizzes Set by Teacher</span>
              </h4>
              <p class="text-xs text-neutral-600">Class completion rates, average scores, and learner participation per assessment.</p>
            </div>
            <span id="quizzesSummaryBadge" class="px-3 py-1 bg-butter border-2 border-black rounded-xl text-xs font-bold self-start">
              4 Quizzes Published
            </span>
          </div>

          <div id="quizzesReportGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Quiz cards injected by JS -->
          </div>
        </div>

        <!-- Study Notes Section -->
        <div class="space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b-2 border-black pb-3">
            <div>
              <h4 class="font-display font-extrabold text-lg sm:text-xl flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-sky border-2 border-black inline-block"></span>
                <span>Study Notes & Learning Resources</span>
              </h4>
              <p class="text-xs text-neutral-600">Learner read rates, total views, and average reading time across uploaded study notes.</p>
            </div>
            <span id="notesSummaryBadge" class="px-3 py-1 bg-sky border-2 border-black rounded-xl text-xs font-bold self-start">
              4 Study Notes Published
            </span>
          </div>

          <div id="notesReportGrid" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Study note cards injected by JS -->
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Quiz / Note Completion Quick Inspector Modal -->
  <div id="resourceInspectorModal" class="fixed inset-0 z-40 hidden bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-cream border-2 border-black shadow-brutal-lg rounded-3xl max-w-2xl w-full max-h-[85vh] flex flex-col overflow-hidden">
      <div class="p-5 bg-butter border-b-2 border-black flex items-center justify-between gap-3">
        <div>
          <span id="inspectorTypeBadge" class="px-2 py-0.5 text-xs font-extrabold uppercase bg-black text-white rounded-md">QUIZ PARTICIPATION</span>
          <h3 id="inspectorTitle" class="font-display font-extrabold text-xl mt-1">Quiz Title</h3>
        </div>
        <button onclick="closeResourceInspector()" class="brutal-btn bg-white w-9 h-9 rounded-xl font-bold flex items-center justify-center cursor-pointer">✕</button>
      </div>
      <div id="inspectorBody" class="p-5 overflow-y-auto space-y-3">
        <!-- Populated dynamically -->
      </div>
    </div>
  </div>

  <!-- Individual Student Subject Report Modal -->
  <div id="studentReportModal" class="fixed inset-0 z-50 hidden bg-black/65 backdrop-blur-xs flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div id="studentReportCard" class="bg-cream border-2 border-black shadow-brutal-lg rounded-3xl max-w-4xl w-full my-auto max-h-[92vh] flex flex-col overflow-hidden">
      
      <!-- Report Modal Top Action Bar -->
      <div class="px-5 py-3.5 bg-white border-b-2 border-black flex flex-wrap items-center justify-between gap-3 no-print">
        <div class="flex items-center gap-2">
          <span class="px-2.5 py-1 text-xs font-extrabold uppercase bg-black text-white rounded-lg">Official Subject Report</span>
          <span id="reportModalTerm" class="text-xs font-bold text-neutral-600">Term 4 • 2026</span>
        </div>
        <div class="flex items-center gap-2">
          <button onclick="printStudentReport()" class="brutal-btn bg-sky hover:bg-sky-300 px-3.5 py-1.5 rounded-xl font-display font-bold text-xs flex items-center gap-1.5 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Print / Export PDF</span>
          </button>
          <button onclick="closeStudentReport()" class="brutal-btn bg-coral hover:bg-rose-300 px-3 py-1.5 rounded-xl font-display font-bold text-xs cursor-pointer">
            Close ✕
          </button>
        </div>
      </div>

      <!-- Report Scrollable Body -->
      <div id="studentReportBody" class="p-5 sm:p-7 overflow-y-auto space-y-6">
        <!-- Populated dynamically via JS -->
      </div>
    </div>
  </div></div>
@endsection