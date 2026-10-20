// Progressive enhancement for the student report. The page is fully readable without this file:
// every subject, every row and every topic is already in the HTML.

const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

// 1. Chart legend: show / hide a subject's line.
$$('[data-chart]').forEach((chart) => {
    $$('[data-series-toggle]', chart).forEach((button) => {
        button.addEventListener('click', () => {
            const show = button.getAttribute('aria-pressed') !== 'true';
            button.setAttribute('aria-pressed', String(show));
            $$('[data-series]', chart)
                .filter((group) => group.dataset.series === button.dataset.seriesToggle)
                .forEach((group) => group.toggleAttribute('hidden', !show));
        });
    });
});

// 2. Subject cards: expand / collapse all.
const cards = $$('details[data-subject-card]');
const toggleAll = document.querySelector('[data-toggle-all]');
if (toggleAll && cards.length) {
    const sync = () => {
        toggleAll.textContent = cards.every((card) => card.open) ? 'Collapse all' : 'Expand all';
    };
    toggleAll.addEventListener('click', () => {
        const open = !cards.every((card) => card.open);
        cards.forEach((card) => (card.open = open));
        sync();
    });
    cards.forEach((card) => card.addEventListener('toggle', sync));
    sync();
}

// 3. Assessments table: filter by subject, show the latest few, "show all" for the rest.
const table = document.querySelector('[data-assessments]');
if (table) {
    const LIMIT = 8;
    const rows = $$('tbody tr', table);
    const chips = $$('[data-filter]');
    const more = document.querySelector('[data-show-more]');
    const empty = document.querySelector('[data-empty]');
    let filter = 'all';
    let expanded = false;

    const render = () => {
        const matching = rows.filter((row) => filter === 'all' || row.dataset.subject === filter);
        rows.forEach((row) => (row.hidden = true));
        matching.forEach((row, index) => (row.hidden = !expanded && index >= LIMIT));
        if (empty) empty.hidden = matching.length > 0;
        if (more) {
            more.hidden = matching.length <= LIMIT;
            more.textContent = expanded ? 'Show fewer' : `Show all ${matching.length}`;
        }
    };

    chips.forEach((chip) =>
        chip.addEventListener('click', () => {
            filter = chip.dataset.filter;
            expanded = false;
            chips.forEach((c) => c.setAttribute('aria-pressed', String(c === chip)));
            render();
        }),
    );
    more?.addEventListener('click', () => {
        expanded = !expanded;
        render();
    });
    render();
}

// 4. Links like "See details" (#subject-accounting) open the subject they point to.
$$('a[href^="#subject-"]').forEach((link) =>
    link.addEventListener('click', () => {
        const target = document.querySelector(link.getAttribute('href'));
        if (target && 'open' in target) target.open = true;
    }),
);

// 5. Theme toggle: switch between GradeGenius Dark and Light themes.
const initThemeToggle = () => {
    const toggles = document.querySelectorAll('#theme-toggle, [data-theme-toggle]');
    toggles.forEach((btn) => {
        btn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('grade_genius_theme', next);
        });
    });
};
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initThemeToggle);
} else {
    initThemeToggle();
}

