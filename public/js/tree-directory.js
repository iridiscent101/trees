(() => {
    const layout = document.querySelector('.directory-layout');

    if (!layout || !window.fetch || !window.DOMParser || !window.history?.pushState) {
        return;
    }

    let activeRequest = null;
    let paginationObserver = null;
    let viewMode = 'grid';
    let searchTimer = null;

    const filterToggle = document.querySelector('.mobile-filter-toggle');
    const setFiltersOpen = (open) => {
        document.body.classList.toggle('filters-open', open);
        filterToggle?.setAttribute('aria-expanded', String(open));

        if (open) {
            document.querySelector('#filter-search')?.focus({ preventScroll: true });
        } else {
            filterToggle?.focus();
        }
    };

    const applyViewMode = () => {
        const results = document.querySelector('#tree-results');
        results?.classList.toggle('list-view', viewMode === 'list');

        document.querySelectorAll('.view-button').forEach((button) => {
            const isActive = button.dataset.view === viewMode;
            button.classList.toggle('active', isActive);
            button.setAttribute('aria-pressed', String(isActive));
        });
    };

    const setLoading = (loading) => {
        layout.classList.toggle('is-loading', loading);
        layout.setAttribute('aria-busy', String(loading));
    };

    const observePaginationDock = () => {
        paginationObserver?.disconnect();

        const slot = document.querySelector('.pagination-pages-slot');
        if (!slot) return;

        if (!window.IntersectionObserver) {
            slot.classList.add('pagination-docked');
            return;
        }

        paginationObserver = new IntersectionObserver(([entry]) => {
            slot.classList.toggle('pagination-docked', entry.isIntersecting);
        }, { threshold: 0.2 });
        paginationObserver.observe(slot);
    };

    const navigate = async (target, { push = true, focusResults = false } = {}) => {
        const url = new URL(target, window.location.href);

        activeRequest?.abort();
        activeRequest = new AbortController();
        const request = activeRequest;
        setLoading(true);

        try {
            const response = await fetch(url, {
                signal: request.signal,
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`Directory request failed with status ${response.status}`);
            }

            const nextDocument = new DOMParser().parseFromString(await response.text(), 'text/html');
            const nextFilters = nextDocument.querySelector('.filters');
            const nextResults = nextDocument.querySelector('.directory-results');

            if (!nextFilters || !nextResults) {
                throw new Error('The directory response is incomplete.');
            }

            document.querySelector('.filters').replaceChildren(...nextFilters.childNodes);
            document.querySelector('.directory-results').replaceChildren(...nextResults.childNodes);
            document.title = nextDocument.title;
            applyViewMode();
            observePaginationDock();

            if (push) {
                window.history.pushState({ directory: true }, '', url);
            }

            if (focusResults) {
                const heading = document.querySelector('#results-heading');
                heading?.setAttribute('tabindex', '-1');
                heading?.focus({ preventScroll: true });
                heading?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                window.location.assign(url);
            }
        } finally {
            if (activeRequest === request) {
                setLoading(false);
            }
        }
    };

    const submitForm = (form) => {
        const params = new URLSearchParams(new FormData(form));
        params.delete('page');
        navigate(`${form.action}?${params.toString()}`);
    };

    document.addEventListener('click', (event) => {
        if (event.target.closest('.public-menu-toggle')) {
            const menu = event.target.closest('.public-menu-toggle');
            const nav = document.querySelector('#public-navigation');
            const open = menu.getAttribute('aria-expanded') === 'true';
            menu.setAttribute('aria-expanded', String(!open));
            nav?.classList.toggle('open', !open);
            return;
        }

        if (event.target.closest('.mobile-filter-toggle')) {
            setFiltersOpen(true);
            return;
        }

        if (event.target.closest('.mobile-filter-close, .filter-backdrop')) {
            setFiltersOpen(false);
            return;
        }

        const viewButton = event.target.closest('.view-button');
        if (viewButton) {
            viewMode = viewButton.dataset.view;
            applyViewMode();
            return;
        }

        const paginationLink = event.target.closest('.pagination a');
        if (paginationLink && !event.ctrlKey && !event.metaKey && !event.shiftKey && !event.altKey) {
            event.preventDefault();
            navigate(paginationLink.href, { focusResults: true });
            return;
        }

        const clearLink = event.target.closest('.filters .clear-button, .empty-results .clear-button');
        if (clearLink) {
            event.preventDefault();
            navigate(clearLink.href);
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('.filters form, .view-controls form');
        if (!form) return;

        event.preventDefault();
        submitForm(form);
        if (form.closest('.filters')) {
            setFiltersOpen(false);
        }
    });

    document.addEventListener('change', (event) => {
        const form = event.target.closest('.filters form, .view-controls form');
        if (!form || event.target.matches('#filter-search')) return;

        submitForm(form);
    });

    document.addEventListener('input', (event) => {
        if (!event.target.matches('#filter-search')) return;

        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => submitForm(event.target.form), 350);
    });

    window.addEventListener('popstate', () => navigate(window.location.href, { push: false }));
    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && document.body.classList.contains('filters-open')) {
            setFiltersOpen(false);
        }
    });

    if (window.location.hash === '#results-heading') {
        window.requestAnimationFrame(() => {
            document.querySelector('#results-heading')?.scrollIntoView({ block: 'start' });
        });
    }

    observePaginationDock();
})();
