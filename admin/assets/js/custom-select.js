/**
 * Tiano Hotel & Suites - Custom Luxury Select Dropdown Component
 * Transforms native <select class="form-select"> into branded, high-end select UI
 * with search filtering, status tags, smooth animations, and full form sync.
 */
(function () {
    'use strict';

    // Helper: Parse option text for parentheses tags like "Room 101 (Clean, Vacant)"
    function parseOptionText(text) {
        if (!text) return { label: '', tags: [] };
        
        // Match pattern: "Main Label (Tag1, Tag2)"
        var match = text.match(/^(.*?)\s*\((.*?)\)\s*$/);
        if (match) {
            var label = match[1].trim();
            var rawTags = match[2].split(',').map(function (t) { return t.trim(); }).filter(Boolean);
            return {
                label: label || text,
                tags: rawTags
            };
        }
        
        return { label: text.trim(), tags: [] };
    }

    // Helper: Determine badge color class based on tag keyword
    function getTagClass(tag) {
        var lower = tag.toLowerCase();
        if (lower.indexOf('clean') !== -1 || lower.indexOf('paid') !== -1 || lower.indexOf('active') !== -1 || lower.indexOf('ready') !== -1) {
            return 'tiano-tag-clean';
        }
        if (lower.indexOf('dirty') !== -1 || lower.indexOf('unpaid') !== -1 || lower.indexOf('cancelled') !== -1 || lower.indexOf('no-show') !== -1) {
            return 'tiano-tag-dirty';
        }
        if (lower.indexOf('inspected') !== -1 || lower.indexOf('reserved') !== -1 || lower.indexOf('pending') !== -1) {
            return 'tiano-tag-inspected';
        }
        if (lower.indexOf('vacant') !== -1 || lower.indexOf('occupied') !== -1 || lower.indexOf('checked-in') !== -1 || lower.indexOf('in-house') !== -1) {
            return 'tiano-tag-vacant';
        }
        if (lower.indexOf('maintenance') !== -1 || lower.indexOf('out of order') !== -1) {
            return 'tiano-tag-maintenance';
        }
        return 'tiano-tag-default';
    }

    // Close all open custom selects
    function closeAllSelects(exceptWrap) {
        var openWraps = document.querySelectorAll('.tiano-select-wrap.is-open');
        openWraps.forEach(function (wrap) {
            if (wrap !== exceptWrap) {
                wrap.classList.remove('is-open');
                wrap.classList.remove('is-dropup');
                var trigger = wrap.querySelector('.tiano-select-trigger');
                if (trigger) trigger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Build trigger HTML based on selected option
    function renderTriggerContent(option) {
        if (!option) return '<span class="tiano-select-value-text text-muted">Select an option...</span>';
        
        var parsed = parseOptionText(option.text);
        var html = '<span class="tiano-select-value-text">' + escapeHtml(parsed.label) + '</span>';
        
        if (parsed.tags.length > 0) {
            html += '<div class="tiano-select-tags-wrap">';
            parsed.tags.forEach(function (t) {
                html += '<span class="tiano-select-tag ' + getTagClass(t) + '">' + escapeHtml(t) + '</span>';
            });
            html += '</div>';
        }
        
        return html;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Initialize a single select element
    function enhanceSelect(select) {
        if (!select || select.dataset.tianoSelectInit === 'true') return;
        if (select.dataset.noCustomSelect === 'true' || select.classList.contains('no-custom-select') || select.closest('.input-group') || select.closest('.filter-bar')) return;
        // Don't enhance multi-selects
        if (select.multiple) return;

        select.dataset.tianoSelectInit = 'true';
        select.classList.add('tiano-native-select-hidden');

        var isSmall = select.classList.contains('form-select-sm') || select.classList.contains('admin-select-sm');

        // Create wrapper
        var wrap = document.createElement('div');
        wrap.className = 'tiano-select-wrap' + (isSmall ? ' is-sm' : '');
        if (select.id) wrap.setAttribute('data-target-id', select.id);

        // Find initial selected option
        var selectedOption = select.options[select.selectedIndex] || select.options[0];

        // Trigger button
        var trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'tiano-select-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');

        var valContainer = document.createElement('div');
        valContainer.className = 'tiano-select-value';
        valContainer.innerHTML = renderTriggerContent(selectedOption);

        var caret = document.createElement('span');
        caret.className = 'tiano-select-caret';
        caret.innerHTML = '<i class="bx bx-chevron-down"></i>';

        trigger.appendChild(valContainer);
        trigger.appendChild(caret);
        wrap.appendChild(trigger);

        // Menu container
        var menu = document.createElement('div');
        menu.className = 'tiano-select-menu';
        menu.setAttribute('role', 'listbox');

        var hasSearch = select.options.length >= 6;
        var searchInput = null;

        if (hasSearch) {
            var searchWrap = document.createElement('div');
            searchWrap.className = 'tiano-select-search-wrap';
            searchWrap.innerHTML = '<i class="bx bx-search"></i>';

            searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.className = 'tiano-select-search-input';
            searchInput.placeholder = 'Search options...';
            searchInput.autocomplete = 'off';

            searchWrap.appendChild(searchInput);
            menu.appendChild(searchWrap);
        }

        var optionsList = document.createElement('div');
        optionsList.className = 'tiano-select-options-list';

        // Render option items
        function buildOptions() {
            optionsList.innerHTML = '';
            for (var i = 0; i < select.options.length; i++) {
                var opt = select.options[i];
                var optItem = document.createElement('div');
                optItem.className = 'tiano-select-option' + (opt.selected ? ' is-selected' : '');
                optItem.setAttribute('data-value', opt.value);
                optItem.setAttribute('data-index', i);
                optItem.setAttribute('role', 'option');
                optItem.setAttribute('aria-selected', opt.selected ? 'true' : 'false');
                if (opt.disabled) {
                    optItem.classList.add('is-disabled');
                }

                var parsed = parseOptionText(opt.text);
                var mainDiv = document.createElement('div');
                mainDiv.className = 'tiano-select-option-main';

                var textSpan = document.createElement('span');
                textSpan.className = 'tiano-select-option-text';
                textSpan.textContent = parsed.label;
                mainDiv.appendChild(textSpan);

                if (parsed.tags.length > 0) {
                    var tagsWrap = document.createElement('div');
                    tagsWrap.className = 'tiano-select-tags-wrap';
                    parsed.tags.forEach(function (t) {
                        var tagSpan = document.createElement('span');
                        tagSpan.className = 'tiano-select-tag ' + getTagClass(t);
                        tagSpan.textContent = t;
                        tagsWrap.appendChild(tagSpan);
                    });
                    mainDiv.appendChild(tagsWrap);
                }

                var checkmark = document.createElement('i');
                checkmark.className = 'bx bx-check tiano-select-checkmark';

                optItem.appendChild(mainDiv);
                optItem.appendChild(checkmark);
                optionsList.appendChild(optItem);
            }
        }

        buildOptions();

        var emptyNotice = document.createElement('div');
        emptyNotice.className = 'tiano-select-empty';
        emptyNotice.style.display = 'none';
        emptyNotice.textContent = 'No matching options found';

        menu.appendChild(optionsList);
        menu.appendChild(emptyNotice);
        wrap.appendChild(menu);

        // Insert wrapper right after native select
        select.parentNode.insertBefore(wrap, select.nextSibling);

        // Smart dropup check
        function checkDropup() {
            var rect = trigger.getBoundingClientRect();
            var menuHeight = Math.min(select.options.length * 44 + 60, 320);
            var spaceBelow = window.innerHeight - rect.bottom;
            var spaceAbove = rect.top;

            if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
                wrap.classList.add('is-dropup');
            } else {
                wrap.classList.remove('is-dropup');
            }
        }

        // Toggle open/close
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (select.disabled) return;

            var isOpen = wrap.classList.contains('is-open');
            closeAllSelects(wrap);

            if (!isOpen) {
                checkDropup();
                wrap.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');

                // Reset search
                if (searchInput) {
                    searchInput.value = '';
                    filterOptions('');
                    setTimeout(function () {
                        searchInput.focus();
                    }, 50);
                }

                // Scroll to selected item
                var selectedItem = optionsList.querySelector('.tiano-select-option.is-selected');
                if (selectedItem) {
                    selectedItem.scrollIntoView({ block: 'nearest' });
                }
            } else {
                wrap.classList.remove('is-open');
                wrap.classList.remove('is-dropup');
                trigger.setAttribute('aria-expanded', 'false');
            }
        });

        // Filter options via search
        function filterOptions(query) {
            var q = query.toLowerCase().trim();
            var items = optionsList.querySelectorAll('.tiano-select-option');
            var visibleCount = 0;

            items.forEach(function (item) {
                var text = (item.textContent || '').toLowerCase();
                if (!q || text.indexOf(q) !== -1) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            emptyNotice.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        if (searchInput) {
            searchInput.addEventListener('input', function (e) {
                filterOptions(e.target.value);
            });
            searchInput.addEventListener('click', function (e) {
                e.stopPropagation();
            });
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    wrap.classList.remove('is-open');
                    trigger.focus();
                }
            });
        }

        // Select an option item
        optionsList.addEventListener('click', function (e) {
            var item = e.target.closest('.tiano-select-option');
            if (!item || item.classList.contains('is-disabled')) return;

            var idx = parseInt(item.getAttribute('data-index'), 10);
            if (!isNaN(idx) && select.options[idx]) {
                select.selectedIndex = idx;

                // Update custom UI
                optionsList.querySelectorAll('.tiano-select-option').forEach(function (el) {
                    el.classList.remove('is-selected');
                    el.setAttribute('aria-selected', 'false');
                });
                item.classList.add('is-selected');
                item.setAttribute('aria-selected', 'true');

                valContainer.innerHTML = renderTriggerContent(select.options[idx]);

                // Close menu
                wrap.classList.remove('is-open');
                wrap.classList.remove('is-dropup');
                trigger.setAttribute('aria-expanded', 'false');
                trigger.focus();

                // Dispatch native events
                select.dispatchEvent(new Event('change', { bubbles: true }));
                select.dispatchEvent(new Event('input', { bubbles: true }));

                // If native select had inline onchange attribute, invoke it
                if (typeof select.onchange === 'function') {
                    select.onchange();
                }
            }
        });

        // Sync when native select value changes from external JS or reset
        select.addEventListener('change', function () {
            var currentOpt = select.options[select.selectedIndex];
            valContainer.innerHTML = renderTriggerContent(currentOpt);

            optionsList.querySelectorAll('.tiano-select-option').forEach(function (el, i) {
                var isSelected = (i === select.selectedIndex);
                el.classList.toggle('is-selected', isSelected);
                el.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            });
        });

        // Handle form reset
        var form = select.closest('form');
        if (form) {
            form.addEventListener('reset', function () {
                setTimeout(function () {
                    var currentOpt = select.options[select.selectedIndex];
                    valContainer.innerHTML = renderTriggerContent(currentOpt);
                    optionsList.querySelectorAll('.tiano-select-option').forEach(function (el, i) {
                        var isSelected = (i === select.selectedIndex);
                        el.classList.toggle('is-selected', isSelected);
                        el.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                    });
                }, 10);
            });
        }

        // Keyboard navigation on trigger
        trigger.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                if (!wrap.classList.contains('is-open')) {
                    trigger.click();
                }
            }
        });
    }

    // Initialize all selects on page
    function initAllCustomSelects() {
        var selects = document.querySelectorAll('select.form-select, select.admin-select');
        selects.forEach(function (sel) {
            enhanceSelect(sel);
        });
    }

    // Global click listener to close dropdowns when clicking outside
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.tiano-select-wrap')) {
            closeAllSelects();
        }
    });

    // Global Escape key listener
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllSelects();
        }
    });

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllCustomSelects);
    } else {
        initAllCustomSelects();
    }

    // Re-check on Bootstrap modal show/shown
    document.addEventListener('show.bs.modal', function () {
        setTimeout(initAllCustomSelects, 60);
    });
    document.addEventListener('shown.bs.modal', function () {
        setTimeout(initAllCustomSelects, 60);
    });

    // MutationObserver to auto-enhance dynamically inserted selects
    if (window.MutationObserver) {
        var observer = new MutationObserver(function (mutations) {
            var shouldInit = false;
            mutations.forEach(function (mutation) {
                if (mutation.addedNodes.length > 0) {
                    for (var i = 0; i < mutation.addedNodes.length; i++) {
                        var node = mutation.addedNodes[i];
                        if (node.nodeType === 1) {
                            if (node.tagName === 'SELECT' || node.querySelector('select')) {
                                shouldInit = true;
                                break;
                            }
                        }
                    }
                }
            });
            if (shouldInit) {
                initAllCustomSelects();
            }
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    // Expose utility globally
    window.TianoSelect = {
        init: initAllCustomSelects,
        enhance: enhanceSelect
    };
})();
